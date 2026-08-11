<?php

namespace MollieForms\Integrations\Rcur;

use MollieForms\Exception;
use MollieForms\MollieApi;
use MollieForms\MollieForms;

class RcurIntegration
{

    const OPTION_ENABLED    = 'mollie_forms_rcur_enabled';
    const OPTION_API_KEY    = 'mollie_forms_rcur_api_key';
    const OPTION_PROFILE_ID = 'mollie_forms_rcur_mollie_profile_id';
    const META_ACTIVE     = '_rfmp_rcur_active';
    const META_FIELD_MAP  = '_rfmp_rcur_field_map';
    const META_SUB_MAP    = '_rfmp_rcur_subscription_map';
    const META_PROFILE_ID = '_rfmp_rcur_mollie_profile';

    const RCUR_CUSTOMER_FIELDS = [
        'first_name', 'last_name', 'email', 'phone', 'address',
        'postal_code', 'city', 'country', 'organization_name',
        'vat_number', 'number', 'locale',
    ];

    private $db, $mollieForms;

    public function __construct($plugin)
    {
        global $wpdb;
        $this->db          = $wpdb;
        $this->mollieForms = $plugin;

        add_action('rfmp_customer_created', [$this, 'onCustomerCreated'], 10, 2);
        add_filter('rfmp_create_subscription', [$this, 'filterCreateSubscription'], 10, 4);
        add_action('rfmp_subscription_stage', [$this, 'onSubscriptionStage'], 10, 5);
    }

    public static function isEnabled()
    {
        return get_option(self::OPTION_ENABLED) && trim((string) get_option(self::OPTION_API_KEY)) !== '';
    }

    public static function getApiKey()
    {
        return trim((string) get_option(self::OPTION_API_KEY));
    }

    public static function isActiveForPost($postId)
    {
        return self::isEnabled() && get_post_meta($postId, self::META_ACTIVE, true);
    }

    /**
     * Hooked on rfmp_customer_created - mirrors the Mollie customer into RCUR.
     *
     * @param int    $postId
     * @param object $mollieCustomer
     */
    public function onCustomerCreated($postId, $mollieCustomer)
    {
        if (!self::isActiveForPost($postId)) {
            return;
        }

        try {
            $payload = $this->buildCustomerPayload($postId, $mollieCustomer);
            if (empty($payload['email']) || empty($payload['first_name']) || empty($payload['last_name'])) {
                $this->log('Customer sync skipped: missing required fields (first_name, last_name, email). Post ' . $postId);
                return;
            }

            $api      = new RcurApi(self::getApiKey());
            $response = $api->createCustomer($payload);

            if (!isset($response->id)) {
                $this->log('Customer sync: unexpected response - no id returned. Post ' . $postId);
                return;
            }

            $localCustomer = $this->db->get_row($this->db->prepare(
                "SELECT * FROM {$this->mollieForms->getCustomersTable()} WHERE post_id = %d AND customer_id = %s ORDER BY id DESC LIMIT 1",
                $postId,
                $mollieCustomer->id
            ));

            if (!$localCustomer) {
                $this->log('Customer sync: local customer not found for Mollie id ' . $mollieCustomer->id);
                return;
            }

            $this->db->insert($this->mollieForms->getRcurCustomersTable(), [
                'created_at'              => current_time('mysql', 1),
                'post_id'                 => $postId,
                'customer_id'             => $localCustomer->id,
                'rcur_customer_id'        => sanitize_text_field($response->id),
                'rcur_mollie_customer_id' => isset($response->mollie_customer_id) ? sanitize_text_field($response->mollie_customer_id) : null,
            ]);
        } catch (Exception $e) {
            $this->log('Customer sync failed: ' . $e->getMessage());
        } catch (\Throwable $t) {
            $this->log('Customer sync error: ' . $t->getMessage());
        }
    }

    /**
     * Returns false to prevent the Mollie-side subscription creation when RCUR is
     * active for this form AND this price option is mapped in the RCUR subscription map.
     */
    public function filterCreateSubscription($create, $postId, $registrationId, $priceOption)
    {
        if (!$create) {
            return false;
        }
        if (!self::isActiveForPost($postId)) {
            return $create;
        }

        $map = get_post_meta($postId, self::META_SUB_MAP, true);
        if (!is_array($map)) {
            return $create;
        }

        $priceOptionId = isset($priceOption->price_option_id) ? (int) $priceOption->price_option_id : 0;
        if ($priceOptionId && isset($map[$priceOptionId]) && !empty($map[$priceOptionId]['enabled'])) {
            return false;
        }

        return $create;
    }

    /**
     * Fires after the Mollie-subscription branch in Webhook.php. Creates the RCUR
     * subscription for each recurring price option whose mapping is enabled.
     */
    public function onSubscriptionStage($postId, $registrationId, $priceOption, $payment, $mollie)
    {
        if (!self::isActiveForPost($postId)) {
            return;
        }

        $map = get_post_meta($postId, self::META_SUB_MAP, true);
        if (!is_array($map)) {
            return;
        }

        $priceOptionId = isset($priceOption->price_option_id) ? (int) $priceOption->price_option_id : 0;
        if (!$priceOptionId || empty($map[$priceOptionId]) || empty($map[$priceOptionId]['enabled'])) {
            return;
        }

        $mapping = $map[$priceOptionId];

        try {
            $registration = $this->db->get_row($this->db->prepare(
                "SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id = %d",
                $registrationId
            ));
            if (!$registration) {
                $this->log('Subscription sync: registration not found ' . $registrationId);
                return;
            }

            $localCustomer = $this->db->get_row($this->db->prepare(
                "SELECT * FROM {$this->mollieForms->getCustomersTable()} WHERE post_id = %d AND customer_id = %s ORDER BY id DESC LIMIT 1",
                $postId,
                $registration->customer_id
            ));
            if (!$localCustomer) {
                $this->log('Subscription sync: local customer not found for ' . $registration->customer_id);
                return;
            }

            $rcurCustomer = $this->db->get_row($this->db->prepare(
                "SELECT * FROM {$this->mollieForms->getRcurCustomersTable()} WHERE customer_id = %d ORDER BY id DESC LIMIT 1",
                $localCustomer->id
            ));
            if (!$rcurCustomer) {
                $this->log('Subscription sync: RCUR customer not found for local id ' . $localCustomer->id);
                return;
            }

            $mandateId = $this->getValidMandateId($mollie, $registration->customer_id, $payment);
            if (!$mandateId) {
                $this->log('Subscription sync: no valid Mollie mandate for customer ' . $registration->customer_id);
                return;
            }

            $profileId = $this->getMollieProfileId($postId);
            if (!$profileId) {
                $this->log('Subscription sync: no Mollie profile selected. Choose one on the form or in the RCUR settings.');
                return;
            }

            $vatSetting = get_post_meta($postId, '_rfmp_vat_setting', true);
            $amount     = (float) $priceOption->price * (int) $priceOption->quantity;
            if ($vatSetting === 'excl') {
                $amount += ((float) $priceOption->vat / 100) * $amount;
            }

            $interval       = $this->resolveInterval($mapping, $priceOption);
            $intervalAmount = isset($mapping['interval_amount']) && $mapping['interval_amount'] !== ''
                ? (int) $mapping['interval_amount']
                : max(1, (int) $priceOption->frequency_value);
            $times          = isset($mapping['times']) && $mapping['times'] !== ''
                ? (int) $mapping['times']
                : (int) $priceOption->times;
            $day            = isset($mapping['day']) && $mapping['day'] !== '' ? (int) $mapping['day'] : null;

            $rule = [
                'amount'          => round($amount, 2),
                'interval'        => $interval,
                'interval_amount' => $intervalAmount,
                'times'           => $times > 0 ? $times : null,
            ];
            if ($day !== null) {
                $rule['day'] = $day;
            }

            $name = !empty($mapping['name'])
                ? sanitize_text_field($mapping['name'])
                : sanitize_text_field($priceOption->description);

            $startDate = $this->calculateStartDate($priceOption);

            $payload = [
                'name'              => $name,
                'customer_id'       => $rcurCustomer->rcur_customer_id,
                'mollie_profile_id' => $profileId,
                'mollie_mandate_id' => $mandateId,
                'start_date'        => $startDate,
                'rules'             => [$rule],
            ];

            $api      = new RcurApi(self::getApiKey());
            $response = $api->createSubscription($payload);

            if (isset($response->id)) {
                $this->db->insert($this->mollieForms->getRcurSubscriptionsTable(), [
                    'created_at'           => current_time('mysql', 1),
                    'registration_id'      => $registrationId,
                    'price_option_id'      => $priceOptionId,
                    'rcur_subscription_id' => sanitize_text_field($response->id),
                ]);
            } else {
                $this->log('Subscription sync: unexpected response - no id returned');
            }
        } catch (Exception $e) {
            $this->log('Subscription sync failed: ' . $e->getMessage());
        } catch (\Throwable $t) {
            $this->log('Subscription sync error: ' . $t->getMessage());
        }
    }

    private function buildCustomerPayload($postId, $mollieCustomer)
    {
        $map        = get_post_meta($postId, self::META_FIELD_MAP, true);
        $fieldTypes = get_post_meta($postId, '_rfmp_fields_type', true) ?: [];

        $payload = [];
        foreach (self::RCUR_CUSTOMER_FIELDS as $rcurField) {
            $value = $this->resolveMappedValue($postId, $fieldTypes, is_array($map) ? $map : [], $rcurField, $mollieCustomer);
            if ($value !== null && $value !== '') {
                $payload[$rcurField] = $value;
            }
        }

        // Fallbacks: make sure first_name/last_name/email are present by deriving from Mollie customer.
        if (empty($payload['email']) && !empty($mollieCustomer->email)) {
            $payload['email'] = sanitize_email($mollieCustomer->email);
        }

        if ((empty($payload['first_name']) || empty($payload['last_name'])) && !empty($mollieCustomer->name)) {
            $parts = preg_split('/\s+/', trim($mollieCustomer->name), 2);
            if (empty($payload['first_name'])) {
                $payload['first_name'] = sanitize_text_field($parts[0]);
            }
            if (empty($payload['last_name'])) {
                $payload['last_name'] = sanitize_text_field(isset($parts[1]) ? $parts[1] : '');
            }
        }

        return $payload;
    }

    private function resolveMappedValue($postId, array $fieldTypes, array $map, $rcurField, $mollieCustomer)
    {
        $mappingTarget = isset($map[$rcurField]) ? $map[$rcurField] : '';

        if ($mappingTarget === '' || $mappingTarget === null) {
            // Smart defaults for the two pieces not directly covered by Mollie name
            if ($rcurField === 'first_name' || $rcurField === 'last_name') {
                return null; // handled by fallback splitter
            }
            return null;
        }

        if (strpos($mappingTarget, 'name:first') === 0 || strpos($mappingTarget, 'name:last') === 0) {
            $parts = explode(':', $mappingTarget, 3);
            $which = $parts[1];
            $index = isset($parts[2]) ? (int) $parts[2] : -1;
            $raw   = $this->readPostField($postId, $index);
            if ($raw === null) {
                return null;
            }
            $split = preg_split('/\s+/', trim($raw), 2);
            if ($which === 'first') {
                return sanitize_text_field($split[0]);
            }
            return sanitize_text_field(isset($split[1]) ? $split[1] : '');
        }

        $index = (int) $mappingTarget;
        $raw   = $this->readPostField($postId, $index);
        if ($raw === null) {
            return null;
        }

        if ($rcurField === 'email') {
            return sanitize_email($raw);
        }
        return sanitize_text_field($raw);
    }

    private function readPostField($postId, $index)
    {
        if ($index < 0) {
            return null;
        }
        $key = 'form_' . $postId . '_field_' . $index;
        if (!isset($_POST[$key])) {
            return null;
        }
        return wp_unslash($_POST[$key]);
    }

    private function resolveInterval($mapping, $priceOption)
    {
        if (!empty($mapping['interval'])) {
            return (string) $mapping['interval'];
        }
        switch ($priceOption->frequency) {
            case 'days':
                return 'day';
            case 'weeks':
                return 'week';
            case 'months':
                return 'month';
        }
        return 'month';
    }

    private function calculateStartDate($priceOption)
    {
        $value = max(1, (int) $priceOption->frequency_value);
        switch ($priceOption->frequency) {
            case 'days':
                $spec = '+' . $value . ' days';
                break;
            case 'weeks':
                $spec = '+' . $value . ' weeks';
                break;
            case 'months':
                $spec = '+' . $value . ' months';
                break;
            default:
                $spec = '+1 months';
        }
        return gmdate('Y-m-d', strtotime($spec, strtotime(gmdate('Y-m-d'))));
    }

    /**
     * @param MollieApi $mollie
     */
    private function getValidMandateId($mollie, $mollieCustomerId, $payment)
    {
        try {
            $response = $mollie->get('customers/' . $mollieCustomerId . '/mandates');
            if (!isset($response->_embedded->mandates)) {
                return null;
            }
            $preferMethod = isset($payment->method) ? $payment->method : null;
            $fallback     = null;
            foreach ($response->_embedded->mandates as $mandate) {
                if (!isset($mandate->status) || $mandate->status !== 'valid') {
                    continue;
                }
                if ($preferMethod && isset($mandate->method) && $mandate->method === $preferMethod) {
                    return $mandate->id;
                }
                if ($fallback === null) {
                    $fallback = $mandate->id;
                }
            }
            return $fallback;
        } catch (Exception $e) {
            $this->log('Mandate lookup failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolves the Mollie profile a subscription must be created under. The profile
     * has to be chosen explicitly: on the form (RCUR metabox) or, as the default for
     * all forms, on the global RCUR settings page. Returns null when nothing is set.
     *
     * @param int|null $postId
     * @return string|null
     */
    public function getMollieProfileId($postId = null)
    {
        // 1. A profile chosen on the form itself (RCUR metabox) wins.
        if ($postId) {
            $formProfile = trim((string) get_post_meta($postId, self::META_PROFILE_ID, true));
            if ($formProfile !== '') {
                return $formProfile;
            }
        }

        // 2. Otherwise the profile chosen on the global RCUR settings page.
        $selected = trim((string) get_option(self::OPTION_PROFILE_ID));
        if ($selected !== '') {
            return $selected;
        }

        return null;
    }

    private function log($message)
    {
        if (function_exists('error_log')) {
            error_log('[Mollie Forms/RCUR] ' . $message);
        }
    }
}

<?php

namespace MollieForms;


use DateTime;

class Webhook
{

    private $db, $mollieForms, $helpers;

    /**
     * Webhook constructor.
     *
     * @param MollieForms $plugin
     */
    public function __construct($plugin)
    {
        global $wpdb;
        $this->db          = $wpdb;
        $this->mollieForms = $plugin;
        $this->helpers     = new Helpers();

        add_filter('query_vars', [$this, 'addQueryVars'], 0);
        add_action('parse_request', [$this, 'sniffRequests'], 0);
        add_action('init', [$this, 'addEndpoint'], 0);
    }

    /**
     * Add public query vars
     *
     * @param array $vars List of current public query vars
     *
     * @return array $vars
     */
    public function addQueryVars($vars)
    {
        $vars[] = '__rfmpapi';
        $vars[] = 'mollie-forms';
        $vars[] = 'post_id';
        $vars[] = 'sub';
        $vars[] = 'first';
        return $vars;
    }

    /**
     * Add API Endpoint
     *
     * @return void
     * @deprecated
     */
    public function addEndpoint()
    {
        add_rewrite_rule('^rfmp-webhook/([0-9]+)/first/([0-9]+)/?', 'index.php?__rfmpapi=1&post_id=$matches[1]&first=$matches[2]', 'top');
        add_rewrite_rule('^rfmp-webhook/([0-9]+)/sub/([0-9]+)/?', 'index.php?__rfmpapi=1&post_id=$matches[1]&sub=$matches[2]', 'top');
        add_rewrite_rule('^rfmp-webhook/([0-9]+)/?', 'index.php?__rfmpapi=1&post_id=$matches[1]', 'top');
        flush_rewrite_rules();
    }

    /**
     * Sniff Requests
     *
     * @param $query
     *
     * @return die if API request
     */
    public function sniffRequests($query)
    {
        if (
            ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($query->query_vars['__rfmpapi'])) ||
            ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($query->query_vars['mollie-forms']))
        ) {
            echo esc_html($this->handleRequest($query));
            exit;
        }
    }

    /**
     * Handle Webhook Request
     *
     * @param $query
     *
     * @return string
     */
    private function handleRequest($query)
    {
        try {
            $post   = $query->query_vars['post_id'];
            $apiKey = get_post_meta($post, '_rfmp_api_key', true);
            $id     = sanitize_text_field($_POST['id']);

            $webhook    = get_home_url(null, $this->mollieForms->getWebhookUrl() . $post);
            $vatSetting = get_post_meta($post, '_rfmp_vat_setting', true);

            $mollie = new MollieApi($apiKey);

            if (substr($id, 0, 3) == 'ord') {
                $order = $mollie->get('orders/' . $id . '?embed=payments');
                foreach ($order->_embedded->payments as $p) {
                    if ($p->status == 'authorized' || $p->status == 'paid') {
                        $payment = $p;
                        break;
                    }
                }
            } else {
                $payment = $mollie->get('payments/' . $id);
            }

            // payment status
            $status = str_replace('canceled', 'cancelled', $payment->status);
            if (!empty($payment->_links->refunds)) {
                $status = 'refunded';
            }

            if (!empty($payment->_links->chargebacks)) {
                $status = 'charged_back';
            }

            do_action('rfmp_webhook_called', $post, $id, $payment);

            $regPayment = $this->db->get_row("SELECT * FROM {$this->mollieForms->getPaymentsTable()} WHERE payment_id = '" .
                                             esc_sql($id) . "'");

            // Recurring payment of subscription
            if (isset($query->query_vars['sub'])) {
                $sub = $this->db->get_row("SELECT * FROM {$this->mollieForms->getSubscriptionsTable()} WHERE id = '" .
                                          esc_sql($query->query_vars['sub']) . "'");
                if ($sub == null) {
                    $sub = $this->db->get_row("SELECT * FROM {$this->mollieForms->getCustomersTable()} WHERE id = '" .
                                              esc_sql($query->query_vars['sub']) . "' AND registration_id != '0'");
                    if ($sub == null) {
                        //status_header(404);
                        return 'Subscription not found';
                    }
                }

                $registration = $this->db->get_row("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id = '" .
                                                   esc_sql($sub->registration_id) . "'");
                if ($registration == null) {
                    //status_header(404);
                    return 'Registration not found';
                }

                if ($regPayment == null) {
                    // Payment not found, add
                    $rfmpId = uniqid('rfmp-' . $post . '-');
                    $this->db->insert($this->mollieForms->getPaymentsTable(), [
                        'created_at'      => current_time('mysql', 1),
                        'registration_id' => $registration->id,
                        'payment_id'      => $id,
                        'payment_method'  => $payment->method,
                        'payment_mode'    => $payment->mode,
                        'payment_status'  => $status,
                        'currency'        => $payment->amount->currency,
                        'amount'          => $payment->amount->value,
                        'rfmp_id'         => $rfmpId,
                    ]);
                } else {
                    // Payment found, update
                    $this->db->update($this->mollieForms->getPaymentsTable(), [
                        'payment_status' => $status,
                        'payment_method' => $payment->method,
                        'payment_mode'   => $payment->mode,
                    ], [
                        'payment_id' => $id,
                    ]);
                }

	            // send emails
	            $this->sendEmail($post, $status, $registration->id, $payment, 'customer');
	            $this->sendEmail($post, $status, $registration->id, $payment, 'merchant');

                return 'OK';
            }

            // check if payment is found in database
            if ($regPayment == null) {
                //status_header(404);
                return 'Payment of registration not found';
            }

            // update payment
            $this->db->update($this->mollieForms->getPaymentsTable(), [
                'payment_status' => $status,
                'payment_method' => $payment->method,
                'payment_mode'   => $payment->mode,
            ], [
                'payment_id' => $id,
            ]);

            // send emails
            $this->sendEmail($post, $status, $regPayment->registration_id, $payment, 'customer');
            $this->sendEmail($post, $status, $regPayment->registration_id, $payment, 'merchant');

            if ($status == 'paid' || $status == 'authorized') {
                // reduce stock
                $priceOptions = $this->db->get_results("SELECT * FROM {$this->mollieForms->getRegistrationPriceOptionsTable()} WHERE price_option_id IS NOT NULL AND registration_id = '" .
                                                       esc_sql($regPayment->registration_id) . "'");

                foreach ($priceOptions as $priceOption) {
                    $this->db->query("UPDATE {$this->mollieForms->getPriceOptionsTable()} SET stock=stock-" .
                                     (int) $priceOption->quantity . " WHERE stock > 0 AND id=" .
                                     (int) $priceOption->price_option_id);
                }

                $registration = $this->db->get_row("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id =" .
                                                   (int) $regPayment->registration_id);
                $fields       = $this->db->get_results("SELECT * FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE registration_id=" .
                                                       (int) $registration->id);

                // update discount code
                foreach ($fields as $field) {
                    if ($field->type == 'discount_code') {
                        $discount = $this->db->get_row("SELECT * FROM {$this->mollieForms->getDiscountCodesTable()} WHERE post_id =" .
                                                       (int) $post . " AND discount_code = '" . esc_sql(trim($field->value)) . "'");

                        if ($discount !== null) {
                            $this->db->query("UPDATE {$this->mollieForms->getDiscountCodesTable()} SET times_used=times_used+1 WHERE id = " .
                                             (int) $discount->id);
                        }
                        break;
                    }
                }

                do_action('rfmp_payment_paid', $post, $registration, $fields);
            }

            // first payment
            if (isset($query->query_vars['first']) && ($payment->status == 'paid' && empty($payment->_links->refunds) &&
                                                       empty($payment->_links->chargebacks))) {
                $registration = $this->db->get_row("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id = '" .
                                                   esc_sql($query->query_vars['first']) . "'");
                if ($registration == null) {
                    return 'Registration not found';
                }

                // subscriptions table fix
                if ($registration->subs_fix) {
                    $subsTable = $this->mollieForms->getSubscriptionsTable();
                } else {
                    $subsTable = $this->mollieForms->getCustomersTable();
                }

                // add subscription for each recurring price option
                $priceOptions = $this->db->get_results("SELECT * FROM {$this->mollieForms->getRegistrationPriceOptionsTable()} WHERE frequency!='once' AND registration_id=" .
                                                       (int) $registration->id);
                foreach ($priceOptions as $priceOption) {
                    // add subscription to database
                    $this->db->insert($subsTable, [
                        'registration_id' => $registration->id,
                        'customer_id'     => $registration->customer_id,
                        'created_at'      => current_time('mysql', 1),
                    ]);
                    $subId = $this->db->insert_id;


                    $optionCurrency = $registration->currency ?: 'EUR';
                    $optionTotal    = $priceOption->price * $priceOption->quantity;
                    $optionVat      = ($priceOption->vat / 100) * $optionTotal;

                    // add VAT to total if price is excl.
                    if ($vatSetting == 'excl') {
                        $optionTotal += $optionVat;
                    }

                    $optionFrequency = ($priceOption->frequency_value ?: 1) . ' ' . $priceOption->frequency;

                    // create Mollie subscription
                    $subscription = $mollie->post('customers/' . $registration->customer_id . '/subscriptions', [
                        "amount"      => [
                            "currency" => $optionCurrency,
                            "value"    => number_format($optionTotal, $this->helpers->getCurrencies($optionCurrency), '.', ''),
                        ],
                        "interval"    => $optionFrequency,
                        "times"       => $priceOption->times > 0 ? ($priceOption->times - 1) : null,
                        "description" => $priceOption->quantity . 'x ' . $priceOption->description,
                        "webhookUrl"  => $webhook . '&sub=' . $subId,
                        "startDate"   => gmdate('Y-m-d', strtotime("+" . $optionFrequency, strtotime(gmdate('Y-m-d')))),
                    ]);

                    // update subscription in database with Mollie data
                    $this->db->update($subsTable, [
                        'subscription_id' => esc_sql(sanitize_text_field($subscription->id)),
                        'sub_mode'        => esc_sql(sanitize_text_field($subscription->mode)),
                        'sub_currency'    => esc_sql(sanitize_text_field($subscription->amount->currency)),
                        'sub_amount'      => esc_sql(sanitize_text_field($subscription->amount->value)),
                        'sub_times'       => esc_sql(sanitize_text_field($subscription->times)),
                        'sub_interval'    => esc_sql(sanitize_text_field($subscription->interval)),
                        'sub_description' => esc_sql(sanitize_text_field($subscription->description)),
                        'sub_method'      => esc_sql(sanitize_text_field($subscription->method)),
                        'sub_status'      => esc_sql(sanitize_text_field($subscription->status)),
                    ], [
                        'id' => $subId,
                    ]);
                }
            }

            return 'OK, ' . $id . ', Post ID: ' . $post;

        } catch (Exception $e) {
            //status_header(500);
            return "API call failed: " . esc_html($e->getMessage());
        }
    }

    private function sendEmail($post, $status, $registrationId, $payment, $type)
    {
	    $status = str_replace('canceled', 'cancelled', $status);
        if (!in_array($status, ['paid', 'expired', 'cancelled', 'charged_back'])) {
            return;
        }

		if ($payment->sequenceType === 'recurring' && $status === 'paid') {
			return;
		}

        $status = str_replace('_', '', $status);

        $enabled = get_post_meta($post, '_rfmp_enabled_' . $status . '_' . $type, true);
        if ($enabled != '1') {
            return;
        }

        $currency   = get_post_meta($post, '_rfmp_currency', true) ?: 'EUR';
        $decimals   = $this->helpers->getCurrencies($currency);
        $symbol     = $this->helpers->getCurrencySymbol($currency);
        $vatSetting = get_post_meta($post, '_rfmp_vat_setting', true);

        $registration = $this->db->get_row($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id=%d", $registrationId));

        $priceOptionString   = [];
        $priceOptionInterval = null;
        $priceOptionTable    = '<table style="width:100%"><tr><td></td><td><strong>' .
                               esc_html__('Description', 'mollie-forms') . '</strong></td><td><strong>' .
                               esc_html__('Price', 'mollie-forms') . '</strong></td><td><strong>' .
                               esc_html__('Subtotal', 'mollie-forms') . '</strong></td></tr>';
        $priceOptionTableVat = '<table style="width:100%"><tr><td></td><td><strong>' .
                               esc_html__('Description', 'mollie-forms') . '</strong></td><td><strong>' .
                               esc_html__('Price', 'mollie-forms') . '</strong></td><td><strong>' . esc_html__('VAT', 'mollie-forms') .
                               '</strong></td><td><strong>' . esc_html__('Subtotal', 'mollie-forms') . '</strong></td></tr>';

        $subtotal = 0;
        $total    = 0;
        $totalVat = 0;

        $priceOptions = $this->db->get_results("SELECT * FROM {$this->mollieForms->getRegistrationPriceOptionsTable()} WHERE registration_id=" .
                                               (int) $registrationId);
        foreach ($priceOptions as $priceOption) {
            $optionPrice = $priceOption->price * $priceOption->quantity;

            if ($vatSetting === 'excl') {
                $optionVat = ($priceOption->vat / 100) * $optionPrice;
                $total     += $optionVat;
            } else {
                $optionVat = ($priceOption->vat / (100 + $priceOption->vat)) * $optionPrice;
            }

            $subtotal += $optionPrice;
            $total    += $optionPrice;
            $totalVat += $optionVat;

            $priceOptionString[] = $priceOption->quantity . 'x ' . $priceOption->description;

            if ($priceOptionInterval === null) {
                $priceOptionInterval = $priceOption->frequency_value . ' ' . $priceOption->frequency;
            }

            $priceOptionTable    .= '<tr><td>' . $priceOption->quantity . 'x</td><td>' . $priceOption->description .
                                    '</td><td>' . $this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') .
                                    ' ' . number_format($priceOption->price, $decimals, ',', '') . ' ' .
                                    $this->helpers->getFrequencyLabel($priceOption->frequency_value . ' ' .
                                                                      $priceOption->frequency) . '</td><td>' .
                                    $this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') . ' ' .
                                    number_format($subtotal, $decimals, ',', '') . '</td></tr>';
            $priceOptionTableVat .= '<tr><td>' . $priceOption->quantity . 'x</td><td>' . $priceOption->description .
                                    '</td><td>' . $this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') .
                                    ' ' . number_format($priceOption->price, $decimals, ',', '') . ' ' .
                                    $this->helpers->getFrequencyLabel($priceOption->frequency_value . ' ' .
                                                                      $priceOption->frequency) . '</td><td>' .
                                    $this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') . ' ' .
                                    number_format($optionVat, $decimals, ',', '') . '</td><td>' .
                                    $this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') . ' ' .
                                    number_format($optionPrice, $decimals, ',', '') . '</td></tr>';
        }

        $priceOptionTable .= '<tr><td colspan="3"><strong>' . __('Total', 'mollie-forms') .
                             '</strong></td><td><strong>' . $symbol . ' ' . number_format($total, $decimals, ',', '') .
                             '</strong></td></tr></table>';


        if ($vatSetting == 'excl') {
            $priceOptionTableVat .= '<tr><td colspan="4"><strong>' . __('Subtotal', 'mollie-forms') .
                                    '</strong></td><td><strong>' . $symbol . ' ' .
                                    number_format($subtotal, $decimals, ',', '') . '</strong></td></tr>';
            $priceOptionTableVat .= '<tr><td colspan="4"><strong>' . __('VAT', 'mollie-forms') .
                                    '</strong></td><td><strong>' . $symbol . ' ' .
                                    number_format($totalVat, $decimals, ',', '') . '</strong></td></tr>';
        }

        $priceOptionTableVat .= '<tr><td colspan="4"><strong>' . __('Total', 'mollie-forms') .
                                '</strong></td><td><strong>' . $symbol . ' ' .
                                number_format($total, $decimals, ',', '') . '</strong></td></tr></table>';

        $createdAt = new DateTime($registration->created_at, wp_timezone());

        $data    = [];
        $search  = [
            '{rfmp="amount"}',
            '{rfmp="interval"}',
            '{rfmp="status"}',
            '{rfmp="payment_id"}',
            '{rfmp="method"}',
            '{rfmp="form_title"}',
            '{rfmp="created_at"}',
            '{rfmp="priceoption"}',
            '{rfmp="priceoption_table"}',
            '{rfmp="priceoption_table_vat"}',
            '{rfmp="url"}',
            '{rfmp="registration_id"}',
        ];
        $replace = [
            $this->helpers->getCurrencySymbol($payment->amount->currency) . ' ' . $payment->amount->value,
            $this->helpers->getFrequencyLabel($priceOptionInterval, true),
            $status,
            $payment->id,
            $payment->method ?: '-',
            get_the_title($post),
            wp_date(get_option('date_format') . ' ' . get_option('time_format'), $createdAt->getTimestamp()),
            implode(', ', $priceOptionString),
            $priceOptionTable,
            $priceOptionTableVat,
            $payment->redirectUrl,
            $registrationId,
        ];

        $fields = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE registration_id=%d", $registrationId));
        foreach ($fields as $row) {
            if ($row->type === 'email') {
                $data['to_email'] = $row->value;
            }

            $data[$row->field] = $row->value;
            $search[]          = '{rfmp="' . trim($row->field) . '"}';

            if ($row->type === 'checkbox') {
                $replace[] = $row->value === '1' ? esc_html__('Yes', 'mollie-forms') : esc_html__('No', 'mollie-forms');
            } else {
                $replace[] = $row->value;
            }
        }

        $email = get_post_meta($post, '_rfmp_email_' . $status . '_' . $type, true);
        $email = str_replace($search, $replace, $email);
        $email = str_replace(['http://', 'https://'], '//', $email);
        $email = str_replace('//', (is_ssl() ? 'https://' : 'http://'), $email);
        $email = nl2br($email);

        $subject = get_post_meta($post, '_rfmp_subject_' . $status . '_' . $type, true);
        $subject = str_replace($search, $replace, $subject);

        $fromname  = get_post_meta($post, '_rfmp_fromname_' . $status . '_' . $type, true);
        $fromemail = get_post_meta($post, '_rfmp_fromemail_' . $status . '_' . $type, true);
        $fromemail = explode(',', trim($fromemail));

        if ($type == 'merchant') {
            $to = get_post_meta($post, '_rfmp_toemail_' . $status . '_' . $type, true) ?: $fromemail;
        } else {
            $to = $data['to_email'];
        }

        $headers[] = 'From: ' . sanitize_text_field($fromname) . ' <' . sanitize_email($fromemail[0]) . '>';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        wp_mail($to, $subject, $email, $headers);
    }

}

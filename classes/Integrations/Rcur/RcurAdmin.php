<?php

namespace MollieForms\Integrations\Rcur;

use MollieForms\MollieForms;

class RcurAdmin
{

    const SETTINGS_PAGE_SLUG = 'mollie-forms-rcur';

    private $db, $mollieForms;

    public function __construct($plugin)
    {
        global $wpdb;
        $this->db          = $wpdb;
        $this->mollieForms = $plugin;

        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_init', [$this, 'registerSettings']);

        if (RcurIntegration::isEnabled()) {
            add_action('add_meta_boxes_mollie-forms', [$this, 'addMetaBox']);
            add_action('save_post_mollie-forms', [$this, 'saveMetaBox'], 20, 2);
        }
    }

    public function registerMenu()
    {
        add_submenu_page(
            'edit.php?post_type=mollie-forms',
            __('RCUR settings', 'mollie-forms'),
            __('RCUR', 'mollie-forms'),
            'manage_options',
            self::SETTINGS_PAGE_SLUG,
            [$this, 'renderSettingsPage']
        );
    }

    public function registerSettings()
    {
        register_setting('mollie_forms_rcur', RcurIntegration::OPTION_ENABLED, [
            'type'              => 'boolean',
            'sanitize_callback' => function ($v) { return $v ? 1 : 0; },
            'default'           => 0,
        ]);
        register_setting('mollie_forms_rcur', RcurIntegration::OPTION_API_KEY, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
        register_setting('mollie_forms_rcur', RcurIntegration::OPTION_PROFILE_ID, [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
        ]);
    }

    public function renderSettingsPage()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Manual "Refresh profiles" action: drop the cached list before fetching.
        if (isset($_GET['rcur_refresh']) && check_admin_referer('mollie_forms_rcur_refresh')) {
            $this->clearProfilesCache();
        }

        $enabled   = (bool) get_option(RcurIntegration::OPTION_ENABLED);
        $apiKey    = (string) get_option(RcurIntegration::OPTION_API_KEY);
        $profileId = (string) get_option(RcurIntegration::OPTION_PROFILE_ID);

        // Fetch the Mollie profiles connected to this RCUR account so the user can
        // pick which one subscriptions are created under.
        $fetched       = $this->fetchRcurProfiles();
        $profiles      = $fetched['profiles'];
        $profilesError = $fetched['error'];

        include $this->mollieForms->getDirPath() . 'templates/admin/settings-rcur.php';
    }

    public function addMetaBox()
    {
        add_meta_box(
            'mollie-forms_rcur',
            __('RCUR integration', 'mollie-forms'),
            [$this, 'renderMetaBox'],
            'mollie-forms',
            'normal',
            'low'
        );
    }

    public function renderMetaBox($post)
    {
        wp_nonce_field('rfmp_meta_box_rcur', 'rfmp_meta_box_rcur_nonce');

        $active       = (bool) get_post_meta($post->ID, RcurIntegration::META_ACTIVE, true);
        $fieldMap     = get_post_meta($post->ID, RcurIntegration::META_FIELD_MAP, true);
        $fieldMap     = is_array($fieldMap) ? $fieldMap : [];
        $subMap       = get_post_meta($post->ID, RcurIntegration::META_SUB_MAP, true);
        $subMap       = is_array($subMap) ? $subMap : [];
        $fieldTypes   = get_post_meta($post->ID, '_rfmp_fields_type', true) ?: [];
        $fieldLabels  = get_post_meta($post->ID, '_rfmp_fields_label', true) ?: [];

        $fieldMap     = $this->applySmartDefaults($fieldMap, $fieldTypes);
        $rcurFields   = $this->rcurFieldDefinitions();
        $formFields   = $this->formFieldOptions($fieldTypes, $fieldLabels);

        $priceOptions = $this->db->get_results($this->db->prepare(
            "SELECT * FROM {$this->mollieForms->getPriceOptionsTable()} WHERE post_id = %d AND frequency != 'once' ORDER BY sort_order ASC, id ASC",
            $post->ID
        ));

        $fetched             = $this->fetchRcurProfiles();
        $mollieProfiles      = $fetched['profiles'];
        $mollieProfilesError = $fetched['error'];
        $selectedProfile     = (string) get_post_meta($post->ID, RcurIntegration::META_PROFILE_ID, true);

        include $this->mollieForms->getDirPath() . 'templates/metaboxes/rcur.php';
    }

    public function saveMetaBox($postId, $post)
    {
        if (!isset($_POST['rfmp_meta_box_rcur_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_rcur_nonce'])), 'rfmp_meta_box_rcur')) {
            return;
        }
        if (!current_user_can('edit_post', $postId)) {
            return;
        }
        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return;
        }

        update_post_meta($postId, RcurIntegration::META_ACTIVE, !empty($_POST['rfmp_rcur_active']) ? 1 : 0);

        update_post_meta(
            $postId,
            RcurIntegration::META_PROFILE_ID,
            isset($_POST['rfmp_rcur_mollie_profile'])
                ? sanitize_text_field(wp_unslash($_POST['rfmp_rcur_mollie_profile']))
                : ''
        );

        $rawMap  = isset($_POST['rfmp_rcur_field_map']) && is_array($_POST['rfmp_rcur_field_map'])
            ? wp_unslash($_POST['rfmp_rcur_field_map'])
            : [];
        $fieldMap = [];
        foreach (RcurIntegration::RCUR_CUSTOMER_FIELDS as $rcurField) {
            if (isset($rawMap[$rcurField])) {
                $fieldMap[$rcurField] = sanitize_text_field($rawMap[$rcurField]);
            }
        }
        update_post_meta($postId, RcurIntegration::META_FIELD_MAP, $fieldMap);

        $rawSub = isset($_POST['rfmp_rcur_sub_map']) && is_array($_POST['rfmp_rcur_sub_map'])
            ? wp_unslash($_POST['rfmp_rcur_sub_map'])
            : [];
        $subMap = [];
        foreach ($rawSub as $priceOptionId => $config) {
            $id = (int) $priceOptionId;
            if ($id <= 0 || !is_array($config)) {
                continue;
            }
            $subMap[$id] = [
                'enabled'         => !empty($config['enabled']) ? 1 : 0,
                'name'            => isset($config['name']) ? sanitize_text_field($config['name']) : '',
                'interval'        => isset($config['interval']) ? sanitize_text_field($config['interval']) : '',
                'interval_amount' => isset($config['interval_amount']) && $config['interval_amount'] !== ''
                    ? (int) $config['interval_amount'] : '',
                'day'             => isset($config['day']) && $config['day'] !== '' ? (int) $config['day'] : '',
                'times'           => isset($config['times']) && $config['times'] !== '' ? (int) $config['times'] : '',
            ];
        }
        update_post_meta($postId, RcurIntegration::META_SUB_MAP, $subMap);
    }

    /**
     * Fetches the Mollie profiles connected to the RCUR account using the global
     * RCUR API key. Shared by the settings page and the per-form metabox.
     *
     * @return array{profiles: array, error: string}
     */
    private function fetchRcurProfiles()
    {
        $apiKey = trim((string) get_option(RcurIntegration::OPTION_API_KEY));
        $result = ['profiles' => [], 'error' => ''];

        if ($apiKey === '') {
            return $result;
        }

        // Cache the (successful) list for 10 minutes so opening a form edit screen
        // does not hit the RCUR API every time. Keyed by the API key so changing
        // the key invalidates the cache automatically.
        $cacheKey = 'mollieforms_rcur_profiles_' . md5($apiKey);
        $cached   = get_transient($cacheKey);
        if (is_array($cached)) {
            $result['profiles'] = $cached;
            return $result;
        }

        try {
            $profiles           = (new RcurApi($apiKey))->listMollieProfiles();
            $result['profiles'] = $profiles;
            set_transient($cacheKey, $profiles, 10 * MINUTE_IN_SECONDS);
        } catch (\Throwable $t) {
            $result['error'] = $t->getMessage();
        }

        return $result;
    }

    /**
     * Clears the cached Mollie profiles list for the current RCUR API key.
     */
    private function clearProfilesCache()
    {
        $apiKey = trim((string) get_option(RcurIntegration::OPTION_API_KEY));
        if ($apiKey !== '') {
            delete_transient('mollieforms_rcur_profiles_' . md5($apiKey));
        }
    }

    private function rcurFieldDefinitions()
    {
        return [
            'first_name'        => [__('First name', 'mollie-forms'), true],
            'last_name'         => [__('Last name', 'mollie-forms'), true],
            'email'             => [__('Email', 'mollie-forms'), true],
            'phone'             => [__('Phone', 'mollie-forms'), false],
            'address'           => [__('Address', 'mollie-forms'), false],
            'postal_code'       => [__('Postal code', 'mollie-forms'), false],
            'city'              => [__('City', 'mollie-forms'), false],
            'country'           => [__('Country', 'mollie-forms'), false],
            'organization_name' => [__('Organization', 'mollie-forms'), false],
            'vat_number'        => [__('VAT number', 'mollie-forms'), false],
            'number'            => [__('Customer number', 'mollie-forms'), false],
            'locale'            => [__('Locale', 'mollie-forms'), false],
        ];
    }

    /**
     * Builds the <option> list for form-field selects, with the special
     * "split name" entries that pull first/last out of a name-field.
     *
     * @return array[] { value, label }
     */
    private function formFieldOptions(array $fieldTypes, array $fieldLabels)
    {
        $options = [];
        foreach ($fieldTypes as $i => $type) {
            $label = isset($fieldLabels[$i]) && $fieldLabels[$i] !== ''
                ? $fieldLabels[$i]
                : ucfirst($type);
            $options[] = ['value' => (string) $i, 'label' => sprintf('%s (%s)', $label, $type)];

            if ($type === 'name') {
                $options[] = [
                    'value' => 'name:first:' . $i,
                    'label' => sprintf(__('%s - first name (split)', 'mollie-forms'), $label),
                ];
                $options[] = [
                    'value' => 'name:last:' . $i,
                    'label' => sprintf(__('%s - last name (split)', 'mollie-forms'), $label),
                ];
            }
        }
        return $options;
    }

    private function applySmartDefaults(array $map, array $fieldTypes)
    {
        $findFirst = function ($type) use ($fieldTypes) {
            foreach ($fieldTypes as $i => $t) {
                if ($t === $type) {
                    return (string) $i;
                }
            }
            return null;
        };

        $defaults = [
            'email'       => $findFirst('email'),
            'address'     => $findFirst('address'),
            'postal_code' => $findFirst('postalCode'),
            'city'        => $findFirst('city'),
            'country'     => $findFirst('country'),
        ];

        $nameIdx = $findFirst('name');
        if ($nameIdx !== null) {
            $defaults['first_name'] = 'name:first:' . $nameIdx;
            $defaults['last_name']  = 'name:last:' . $nameIdx;
        }

        foreach ($defaults as $rcurField => $value) {
            if ($value !== null && (!isset($map[$rcurField]) || $map[$rcurField] === '')) {
                $map[$rcurField] = $value;
            }
        }

        return $map;
    }
}

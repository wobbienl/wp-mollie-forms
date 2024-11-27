<?php

namespace MollieForms;


class Admin
{

    private $db, $mollieForms, $helpers;

    /**
     * Admin constructor.
     *
     * @param MollieForms $plugin
     */
    public function __construct($plugin)
    {
        global $wpdb;

        $this->db          = $wpdb;
        $this->mollieForms = $plugin;
        $this->helpers     = new Helpers();
        $this->initAdmin();
    }

    /**
     * Init admin
     */
    private function initAdmin()
    {
        add_action('init', function() {
            remove_post_type_support('mollie-forms', 'editor');
        });
        add_action('admin_menu', [$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [$this, 'loadScripts']);
        add_action('add_meta_boxes_mollie-forms', [$this, 'addMetaBoxes']);
        add_action('save_post_mollie-forms', [$this, 'saveMetaBoxes'], 10, 2);
        add_action('admin_post_mollie-forms_export', [$this, 'exportRegistrations']);
        add_action('admin_post_mollie-forms_duplicate', [$this, 'duplicateForm']);

        add_filter('plugin_row_meta', [$this, 'pluginRowMeta'], 10, 2);
        add_filter('post_row_actions', [$this, 'postActions'], 10, 2);

	    add_action('admin_notices', function() {
			$nrFormsWithSiteKey = $this->db->get_var("SELECT COUNT(*) FROM {$this->db->prefix}postmeta pmsite LEFT JOIN {$this->db->prefix}postmeta pmsec ON pmsec.post_id = pmsite.post_id WHERE pmsite.meta_key = '_rfmp_recaptcha_v3_site_key' AND pmsite.meta_value != '' AND pmsec.meta_key = '_rfmp_recaptcha_v3_secret_key' AND pmsec.meta_value = ''");
			if ($nrFormsWithSiteKey > 0) {
				echo '<div class="notice notice-warning is-dismissible">
			             <p>We hebben de Google reCaptcha integratie in Mollie Forms aangepast. Om Google reCaptcha werkend te houden is het nu ook nodig de geheime sleutel in te voeren in alle formulieren die hier gebruik van maken.</p>
			             <p>We have updated the Google reCaptcha integration in Mollie Forms. To make sure the integration keeps working, please add your secret key to all forms using Google reCaptcha.</p>
			         </div>';
			}
	    });
    }

    /**
     * Add items to Wordpress menu
     */
    public function adminMenu()
    {
        // Registrations
        add_submenu_page(
                'edit.php?post_type=mollie-forms',
                __('Registrations', 'mollie-forms'),
                __('Registrations', 'mollie-forms'),
                'edit_posts',
                'registrations',
                [
                        $this,
                        'pageRegistrations',
                ]
        );

        // Single registration
        add_submenu_page(
                null,
                __('Registration', 'mollie-forms'),
                __('Registration', 'mollie-forms'),
                'edit_posts',
                'registration',
                [
                        $this,
                        'pageRegistration',
                ]
        );

        // Add-ons
        add_submenu_page(
                'edit.php?post_type=mollie-forms',
                __('Add-ons', 'mollie-forms'),
                __('Add-ons', 'mollie-forms'),
                'administrator',
                'add-ons',
                [
                        $this,
                        'pageAddons',
                ]
        );

        // Support
        global $submenu;
        $submenu['edit.php?post_type=mollie-forms'][] = [__('Support', 'mollie-forms'),
                                                         'manage_options',
                                                         'https://support.wobbie.nl',
        ];
        $submenu['edit.php?post_type=mollie-forms'][] = [__('Feature requests', 'mollie-forms'),
                                                         'manage_options',
                                                         'https://features.wobbie.nl',
        ];
        $submenu['edit.php?post_type=mollie-forms'][] = [__('Donate', 'mollie-forms'),
                                                         'manage_options',
                                                         'https://wobbie.nl/doneren',
        ];
    }

    /**
     * Add links to plugin meta
     *
     * @param $links
     * @param $file
     *
     * @return array
     */
    public function pluginRowMeta($links, $file)
    {
        if ($this->mollieForms->getBaseName() == $file) {
            $row_meta = [
                    'support'  => '<a href="https://support.wobbie.nl" target="_blank">' .
                                  esc_html__('Support', 'mollie-forms') . '</a>',
                    'features' => '<a href="https://features.wobbie.nl" target="_blank">' .
                                  esc_html__('Feature requests', 'mollie-forms') . '</a>',
                    'add-ons'  => '<a href="edit.php?post_type=mollie-forms&page=add-ons">' .
                                  esc_html__('Add-ons', 'mollie-forms') . '</a>',
                    'donate'   => '<a href="https://wobbie.nl/doneren" target="_blank">' .
                                  esc_html__('Donate', 'mollie-forms') . '</a>',
            ];

            return array_merge($links, $row_meta);
        }

        return (array) $links;
    }

    /**
     * Load JavaScripts and Stylesheets
     */
    public function loadScripts()
    {
	    wp_enqueue_script(
		    'mollie-forms_admin_scripts',
		    $this->mollieForms->getDirUrl() . 'includes/js/admin-scripts.js',
		    [ 'jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-tabs' ],
		    $this->mollieForms->getVersion(),
		    [ 'in_footer' => true ]
	    );
	    wp_enqueue_style(
		    'mollie-forms_admin_styles',
		    $this->mollieForms->getDirUrl() . 'includes/css/admin-styles.css',
		    [],
		    $this->mollieForms->getVersion()
	    );

	    wp_register_style(
		    'jQueryUI',
		    $this->mollieForms->getDirUrl() . 'includes/css/jquery-ui.css',
		    [],
		    $this->mollieForms->getVersion()
	    );
	    wp_enqueue_style( 'jQueryUI', '', [], $this->mollieForms->getVersion() );
    }

    /**
     * Replace the actions of a Wordpress Post
     *
     * @param $actions
     * @param $post
     *
     * @return mixed
     */
    public function postActions($actions, $post)
    {
        if ($post->post_type == 'mollie-forms') {
            unset($actions['inline hide-if-no-js']);
            unset($actions['view']);
            $actions['registrations'] = '<a href="' . wp_nonce_url(admin_url('edit.php?post_type=mollie-forms&page=registrations&post=' . $post->ID), 'search-mollie-forms-registrations') . '">' . __('Registrations', 'mollie-forms') . '</a>';
            $actions['export']        = '<a href="' .
                                        wp_nonce_url(admin_url('admin-post.php?action=mollie-forms_export&post=' . $post->ID), 'export-mollie-forms-registrations') .
                                        '">' . __('Export', 'mollie-forms') . '</a>';
            $actions['duplicate']     = '<a href="' .
                                        wp_nonce_url(admin_url('admin-post.php?action=mollie-forms_duplicate&post=' . $post->ID), 'duplicate-mollie-forms-form') .
                                        '">' . __('Duplicate', 'mollie-forms') . '</a>';
        }
        return $actions;
    }

    /**
     * Register post meta boxes
     *
     * @param $post
     */
    public function addMetaBoxes($post)
    {
        add_meta_box('mollie-forms_fields', __('Fields', 'mollie-forms'), [
                $this,
                'metaBoxFields',
        ], 'mollie-forms', 'normal', 'high');
        add_meta_box('mollie-forms_settings', __('Settings', 'mollie-forms'), [
                $this,
                'metaBoxSettings',
        ], 'mollie-forms', 'normal', 'default');
        add_meta_box('mollie-forms_priceoptions', __('Price options', 'mollie-forms'), [
                $this,
                'metaBoxPriceOptions',
        ], 'mollie-forms', 'normal', 'default');
        add_meta_box('mollie-forms_discountcodes', __('Discount codes', 'mollie-forms'), [
                $this,
                'metaBoxDiscountCodes',
        ], 'mollie-forms', 'normal', 'default');
        add_meta_box('mollie-forms_emails', __('Email settings', 'mollie-forms'), [
                $this,
                'metaBoxEmails',
        ], 'mollie-forms', 'normal', 'default');
        add_meta_box('mollie-forms_paymentmethods', __('Payment methods', 'mollie-forms'), [
                $this,
                'metaBoxPaymentMethods',
        ], 'mollie-forms', 'side', 'default');
        remove_meta_box('slugdiv', 'mollie-forms', 'normal');
    }

    /**
     * Form fields meta box
     *
     * @param $post
     */
    public function metaBoxFields($post)
    {
        wp_nonce_field(basename(__FILE__), 'rfmp_meta_box_fields_nonce');
        $api_type       = get_post_meta($post->ID, '_rfmp_api_type', true) ?: 'payments';
        $field_type     = get_post_meta($post->ID, '_rfmp_fields_type', true);
        $field_label    = get_post_meta($post->ID, '_rfmp_fields_label', true);
        $field_value    = get_post_meta($post->ID, '_rfmp_fields_value', true);
        $field_class    = get_post_meta($post->ID, '_rfmp_fields_class', true);
        $field_required = get_post_meta($post->ID, '_rfmp_fields_required', true);

        if (empty($field_type)) {
            $field_type  = [0 => 'name',
                            1 => 'email',
                            2 => 'priceoptions',
                            3 => 'payment_methods',
                            4 => 'total',
                            5 => 'discount_code',
                            6 => 'submit',
            ];
            $field_label = [0 => __('Name', 'mollie-forms'),
                            1 => __('Email', 'mollie-forms'),
                            2 => '',
                            3 => __('Payment method', 'mollie-forms'),
                            5 => __('Discount code', 'mollie-forms'),
                            6 => __('Submit', 'mollie-forms'),
            ];
        }

        include $this->mollieForms->getDirPath() . 'templates/metaboxes/fields.php';
    }

    /**
     * Form settings meta box
     *
     * @param $post
     */
    public function metaBoxSettings($post)
    {
        wp_nonce_field(basename(__FILE__), 'rfmp_meta_box_settings_nonce');
        $api_key             = get_post_meta($post->ID, '_rfmp_api_key', true);
        $api_type            = get_post_meta($post->ID, '_rfmp_api_type', true);
        $display_label       = get_post_meta($post->ID, '_rfmp_label_display', true);
        $display_pm          = get_post_meta($post->ID, '_rfmp_payment_methods_display', true);
        $display_po          = get_post_meta($post->ID, '_rfmp_priceoptions_display', true);
        $class_success       = get_post_meta($post->ID, '_rfmp_class_success', true);
        $class_error         = get_post_meta($post->ID, '_rfmp_class_error', true);
        $payment_description = get_post_meta($post->ID, '_rfmp_payment_description', true);
        $after_payment       = get_post_meta($post->ID, '_rfmp_after_payment', true);
        $message_success     = get_post_meta($post->ID, '_rfmp_msg_success', true);
        $message_error       = get_post_meta($post->ID, '_rfmp_msg_error', true);
        $redirect_success    = get_post_meta($post->ID, '_rfmp_redirect_success', true);
        $redirect_error      = get_post_meta($post->ID, '_rfmp_redirect_error', true);
        $class_form          = get_post_meta($post->ID, '_rfmp_class_form', true);
        $currency            = get_post_meta($post->ID, '_rfmp_currency', true);
        $locale              = get_post_meta($post->ID, '_rfmp_locale', true);
        $shippingCosts       = get_post_meta($post->ID, '_rfmp_shipping_costs', true);
        $vatSetting          = get_post_meta($post->ID, '_rfmp_vat_setting', true);
        $recaptchaSiteKey    = get_post_meta($post->ID, '_rfmp_recaptcha_v3_site_key', true);
        $recaptchaSecretKey  = get_post_meta($post->ID, '_rfmp_recaptcha_v3_secret_key', true);
        $recaptchaScore      = get_post_meta($post->ID, '_rfmp_recaptcha_v3_minimum_score', true) ?: MollieForms::DEFAULT_MINIMUM_RECAPTCHA_SCORE;

        include $this->mollieForms->getDirPath() . 'templates/metaboxes/settings.php';
    }

    /**
     * Price options meta box
     *
     * @param $post
     */
    public function metaBoxPriceOptions($post)
    {
        wp_nonce_field(basename(__FILE__), 'rfmp_meta_box_priceoptions_nonce');
        $priceOptions = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getPriceOptionsTable()} WHERE post_id=%d ORDER BY sort_order ASC", $post->ID));

        include $this->mollieForms->getDirPath() . 'templates/metaboxes/priceOptions.php';
    }

    /**
     * Emails meta box
     *
     * @param $post
     */
    public function metaBoxEmails($post)
    {
        wp_nonce_field(basename(__FILE__), 'rfmp_meta_box_emails_nonce');

        $enabled_paid_customer   = get_post_meta($post->ID, '_rfmp_enabled_paid_customer', true);
        $email_paid_customer     = get_post_meta($post->ID, '_rfmp_email_paid_customer', true);
        $subject_paid_customer   = get_post_meta($post->ID, '_rfmp_subject_paid_customer', true);
        $fromemail_paid_customer = get_post_meta($post->ID, '_rfmp_fromemail_paid_customer', true);
        $fromname_paid_customer  = get_post_meta($post->ID, '_rfmp_fromname_paid_customer', true);

        $enabled_expired_customer   = get_post_meta($post->ID, '_rfmp_enabled_expired_customer', true);
        $email_expired_customer     = get_post_meta($post->ID, '_rfmp_email_expired_customer', true);
        $subject_expired_customer   = get_post_meta($post->ID, '_rfmp_subject_expired_customer', true);
        $fromemail_expired_customer = get_post_meta($post->ID, '_rfmp_fromemail_expired_customer', true);
        $fromname_expired_customer  = get_post_meta($post->ID, '_rfmp_fromname_expired_customer', true);

        $enabled_cancelled_customer   = get_post_meta($post->ID, '_rfmp_enabled_cancelled_customer', true);
        $email_cancelled_customer     = get_post_meta($post->ID, '_rfmp_email_cancelled_customer', true);
        $subject_cancelled_customer   = get_post_meta($post->ID, '_rfmp_subject_cancelled_customer', true);
        $fromemail_cancelled_customer = get_post_meta($post->ID, '_rfmp_fromemail_cancelled_customer', true);
        $fromname_cancelled_customer  = get_post_meta($post->ID, '_rfmp_fromname_cancelled_customer', true);

        $enabled_chargedback_customer   = get_post_meta($post->ID, '_rfmp_enabled_chargedback_customer', true);
        $email_chargedback_customer     = get_post_meta($post->ID, '_rfmp_email_chargedback_customer', true);
        $subject_chargedback_customer   = get_post_meta($post->ID, '_rfmp_subject_chargedback_customer', true);
        $fromemail_chargedback_customer = get_post_meta($post->ID, '_rfmp_fromemail_chargedback_customer', true);
        $fromname_chargedback_customer  = get_post_meta($post->ID, '_rfmp_fromname_chargedback_customer', true);

        $enabled_paid_merchant   = get_post_meta($post->ID, '_rfmp_enabled_paid_merchant', true);
        $email_paid_merchant     = get_post_meta($post->ID, '_rfmp_email_paid_merchant', true);
        $subject_paid_merchant   = get_post_meta($post->ID, '_rfmp_subject_paid_merchant', true);
        $fromemail_paid_merchant = get_post_meta($post->ID, '_rfmp_fromemail_paid_merchant', true);
        $fromname_paid_merchant  = get_post_meta($post->ID, '_rfmp_fromname_paid_merchant', true);
        $toemail_paid_merchant   = get_post_meta($post->ID, '_rfmp_toemail_paid_merchant', true);

        $enabled_expired_merchant   = get_post_meta($post->ID, '_rfmp_enabled_expired_merchant', true);
        $email_expired_merchant     = get_post_meta($post->ID, '_rfmp_email_expired_merchant', true);
        $subject_expired_merchant   = get_post_meta($post->ID, '_rfmp_subject_expired_merchant', true);
        $fromemail_expired_merchant = get_post_meta($post->ID, '_rfmp_fromemail_expired_merchant', true);
        $fromname_expired_merchant  = get_post_meta($post->ID, '_rfmp_fromname_expired_merchant', true);
        $toemail_expired_merchant   = get_post_meta($post->ID, '_rfmp_toemail_expired_merchant', true);

        $enabled_cancelled_merchant   = get_post_meta($post->ID, '_rfmp_enabled_cancelled_merchant', true);
        $email_cancelled_merchant     = get_post_meta($post->ID, '_rfmp_email_cancelled_merchant', true);
        $subject_cancelled_merchant   = get_post_meta($post->ID, '_rfmp_subject_cancelled_merchant', true);
        $fromemail_cancelled_merchant = get_post_meta($post->ID, '_rfmp_fromemail_cancelled_merchant', true);
        $fromname_cancelled_merchant  = get_post_meta($post->ID, '_rfmp_fromname_cancelled_merchant', true);
        $toemail_cancelled_merchant   = get_post_meta($post->ID, '_rfmp_toemail_cancelled_merchant', true);

        $enabled_chargedback_merchant   = get_post_meta($post->ID, '_rfmp_enabled_chargedback_merchant', true);
        $email_chargedback_merchant     = get_post_meta($post->ID, '_rfmp_email_chargedback_merchant', true);
        $subject_chargedback_merchant   = get_post_meta($post->ID, '_rfmp_subject_chargedback_merchant', true);
        $fromemail_chargedback_merchant = get_post_meta($post->ID, '_rfmp_fromemail_chargedback_merchant', true);
        $fromname_chargedback_merchant  = get_post_meta($post->ID, '_rfmp_fromname_chargedback_merchant', true);
        $toemail_chargedback_merchant   = get_post_meta($post->ID, '_rfmp_toemail_chargedback_merchant', true);

        $rfmp_editor_settings = [];

        include $this->mollieForms->getDirPath() . 'templates/metaboxes/emails.php';
    }

    public function metaBoxPaymentMethods($post)
    {
        wp_nonce_field(basename(__FILE__), 'rfmp_meta_box_paymentmethods_nonce');
        $api_key  = get_post_meta($post->ID, '_rfmp_api_key', true);
        $api_type = get_post_meta($post->ID, '_rfmp_api_type', true) ?: 'payments';
        $active   = get_post_meta($post->ID, '_rfmp_payment_method', true);
        $fixed    = get_post_meta($post->ID, '_rfmp_payment_method_fixed', true);
        $variable = get_post_meta($post->ID, '_rfmp_payment_method_variable', true);
        $currency = get_post_meta($post->ID, '_rfmp_currency', true) ?: 'EUR';
        $locale   = get_post_meta($post->ID, '_rfmp_locale', true) ?: null;

        if (!$locale && key_exists(get_locale(), $this->helpers->getLocales())) {
            $locale = get_locale();
        }

        try {

            if (!$api_key) {
                echo '<p style="color: red">' . esc_html__('No API-key set', 'mollie-forms') . '</p>';
            } else {
                $mollie = new MollieApi($api_key);

                $methods = $mollie->all('methods', [
                        'locale'         => $locale,
                        'resource'       => $api_type,
                        'includeWallets' => 'applepay',
                        'amount'         => ['value' => '1.00', 'currency' => $currency],
                ]);

                foreach ($methods as $method) {
                    include $this->mollieForms->getDirPath() . 'templates/metaboxes/paymentMethod.php';
                }
            }

        } catch (Exception $e) {
            echo '<p style="color: red">' . esc_html(sanitize_text_field($e->getMessage())) . '</p>';
        }
    }

    public function metaBoxDiscountCodes($post)
    {
        wp_nonce_field(basename(__FILE__), 'rfmp_meta_box_discountcodes_nonce');

        $discountCodes = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getDiscountCodesTable()} WHERE post_id=%d", $post->ID));

        include $this->mollieForms->getDirPath() . 'templates/metaboxes/discountCodes.php';
    }

    /**
     * Save meta boxes
     *
     * @param $postId
     */
    public function saveMetaBoxes($postId)
    {
        if (!isset($_POST['rfmp_meta_box_fields_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_fields_nonce'])), basename(__FILE__))) {
            return;
        }

        if (!isset($_POST['rfmp_meta_box_priceoptions_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_priceoptions_nonce'])), basename(__FILE__))) {
            return;
        }

        if (!isset($_POST['rfmp_meta_box_settings_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_settings_nonce'])), basename(__FILE__))) {
            return;
        }

        if (!isset($_POST['rfmp_meta_box_paymentmethods_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_paymentmethods_nonce'])), basename(__FILE__))) {
            return;
        }

        if (!isset($_POST['rfmp_meta_box_emails_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_emails_nonce'])), basename(__FILE__))) {
            return;
        }

        if (!isset($_POST['rfmp_meta_box_discountcodes_nonce']) ||
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rfmp_meta_box_discountcodes_nonce'])), basename(__FILE__))) {
            return;
        }

        // Check the user's permissions.
        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        // Store custom fields
        update_post_meta($postId, '_rfmp_api_key', sanitize_text_field($_POST['rfmp_api_key']));
        update_post_meta($postId, '_rfmp_api_type', sanitize_text_field($_POST['rfmp_api_type']));
        update_post_meta($postId, '_rfmp_label_display', sanitize_text_field($_POST['rfmp_label_display']));
        update_post_meta($postId, '_rfmp_payment_methods_display', sanitize_text_field($_POST['rfmp_payment_methods_display']));
        update_post_meta($postId, '_rfmp_priceoptions_display', sanitize_text_field($_POST['rfmp_priceoptions_display']));
        update_post_meta($postId, '_rfmp_after_payment', sanitize_text_field($_POST['rfmp_after_payment']));
        update_post_meta($postId, '_rfmp_redirect_success', sanitize_text_field($_POST['rfmp_redirect_success']));
        update_post_meta($postId, '_rfmp_redirect_error', sanitize_text_field($_POST['rfmp_redirect_error']));
        update_post_meta($postId, '_rfmp_class_success', sanitize_text_field($_POST['rfmp_class_success']));
        update_post_meta($postId, '_rfmp_class_error', sanitize_text_field($_POST['rfmp_class_error']));
        update_post_meta($postId, '_rfmp_payment_description', sanitize_text_field($_POST['rfmp_payment_description']));
        update_post_meta($postId, '_rfmp_msg_success', sanitize_text_field($_POST['rfmp_msg_success']));
        update_post_meta($postId, '_rfmp_msg_error', sanitize_text_field($_POST['rfmp_msg_error']));
        update_post_meta($postId, '_rfmp_class_form', sanitize_text_field($_POST['rfmp_class_form']));
        update_post_meta($postId, '_rfmp_locale', sanitize_text_field($_POST['rfmp_locale']));
        update_post_meta($postId, '_rfmp_currency', sanitize_text_field($_POST['rfmp_currency']));
        update_post_meta($postId, '_rfmp_shipping_costs', sanitize_text_field($_POST['rfmp_shipping_costs']));
        update_post_meta($postId, '_rfmp_vat_setting', sanitize_text_field($_POST['rfmp_vat_setting']));
        update_post_meta($postId, '_rfmp_recaptcha_v3_site_key', sanitize_text_field($_POST['rfmp_recaptcha_v3_site_key']));
        update_post_meta($postId, '_rfmp_recaptcha_v3_secret_key', sanitize_text_field($_POST['rfmp_recaptcha_v3_secret_key']));
        update_post_meta($postId, '_rfmp_recaptcha_v3_minimum_score', sanitize_text_field($_POST['rfmp_recaptcha_v3_minimum_score']));

        // Add address fields when API type is "orders"
        if ($_POST['rfmp_api_type'] == 'orders') {
            if (!in_array('address', $_POST['rfmp_fields_type'])) {
                $_POST['rfmp_fields_type']     = array_merge($_POST['rfmp_fields_type'], ['address']);
                $_POST['rfmp_fields_label']    = array_merge($_POST['rfmp_fields_label'], [__('Address', 'mollie-forms')]);
                $_POST['rfmp_fields_value']    = array_merge($_POST['rfmp_fields_value'], ['']);
                $_POST['rfmp_fields_class']    = array_merge($_POST['rfmp_fields_class'], ['']);
                $_POST['rfmp_fields_required'] = array_merge($_POST['rfmp_fields_required'], [1]);
            }
            if (!in_array('postalCode', $_POST['rfmp_fields_type'])) {
                $_POST['rfmp_fields_type']     = array_merge($_POST['rfmp_fields_type'], ['postalCode']);
                $_POST['rfmp_fields_label']    = array_merge($_POST['rfmp_fields_label'], [__('Postal code', 'mollie-forms')]);
                $_POST['rfmp_fields_value']    = array_merge($_POST['rfmp_fields_value'], ['']);
                $_POST['rfmp_fields_class']    = array_merge($_POST['rfmp_fields_class'], ['']);
                $_POST['rfmp_fields_required'] = array_merge($_POST['rfmp_fields_required'], [1]);
            }
            if (!in_array('city', $_POST['rfmp_fields_type'])) {
                $_POST['rfmp_fields_type']     = array_merge($_POST['rfmp_fields_type'], ['city']);
                $_POST['rfmp_fields_label']    = array_merge($_POST['rfmp_fields_label'], [__('City', 'mollie-forms')]);
                $_POST['rfmp_fields_value']    = array_merge($_POST['rfmp_fields_value'], ['']);
                $_POST['rfmp_fields_class']    = array_merge($_POST['rfmp_fields_class'], ['']);
                $_POST['rfmp_fields_required'] = array_merge($_POST['rfmp_fields_required'], [1]);
            }
            if (!in_array('country', $_POST['rfmp_fields_type'])) {
                $_POST['rfmp_fields_type']     = array_merge($_POST['rfmp_fields_type'], ['country']);
                $_POST['rfmp_fields_label']    = array_merge($_POST['rfmp_fields_label'], [__('Country', 'mollie-forms')]);
                $_POST['rfmp_fields_value']    = array_merge($_POST['rfmp_fields_value'], ['']);
                $_POST['rfmp_fields_class']    = array_merge($_POST['rfmp_fields_class'], ['']);
                $_POST['rfmp_fields_required'] = array_merge($_POST['rfmp_fields_required'], [1]);
            }
        }

        update_post_meta($postId, '_rfmp_fields_type', $_POST['rfmp_fields_type']);
        update_post_meta($postId, '_rfmp_fields_label', $_POST['rfmp_fields_label']);
        update_post_meta($postId, '_rfmp_fields_value', $_POST['rfmp_fields_value']);
        update_post_meta($postId, '_rfmp_fields_class', $_POST['rfmp_fields_class']);
        update_post_meta($postId, '_rfmp_fields_required', $_POST['rfmp_fields_required']);

        update_post_meta($postId, '_rfmp_payment_method', $_POST['rfmp_payment_method']);
        update_post_meta($postId, '_rfmp_payment_method_fixed', $_POST['rfmp_payment_method_fixed']);
        update_post_meta($postId, '_rfmp_payment_method_variable', $_POST['rfmp_payment_method_variable']);

        update_post_meta($postId, '_rfmp_enabled_paid_customer', ($_POST['rfmp_enabled_paid_customer']));
        update_post_meta($postId, '_rfmp_email_paid_customer', $_POST['rfmp_email_paid_customer']);
        update_post_meta($postId, '_rfmp_subject_paid_customer', ($_POST['rfmp_subject_paid_customer']));
        update_post_meta($postId, '_rfmp_fromname_paid_customer', ($_POST['rfmp_fromname_paid_customer']));
        update_post_meta($postId, '_rfmp_fromemail_paid_customer', $_POST['rfmp_fromemail_paid_customer']);
        update_post_meta($postId, '_rfmp_enabled_expired_customer', ($_POST['rfmp_enabled_expired_customer']));
        update_post_meta($postId, '_rfmp_email_expired_customer', ($_POST['rfmp_email_expired_customer']));
        update_post_meta($postId, '_rfmp_subject_expired_customer', ($_POST['rfmp_subject_expired_customer']));
        update_post_meta($postId, '_rfmp_fromname_expired_customer', ($_POST['rfmp_fromname_expired_customer']));
        update_post_meta($postId, '_rfmp_fromemail_expired_customer', $_POST['rfmp_fromemail_expired_customer']);
        update_post_meta($postId, '_rfmp_enabled_cancelled_customer', ($_POST['rfmp_enabled_cancelled_customer']));
        update_post_meta($postId, '_rfmp_email_cancelled_customer', ($_POST['rfmp_email_cancelled_customer']));
        update_post_meta($postId, '_rfmp_subject_cancelled_customer', ($_POST['rfmp_subject_cancelled_customer']));
        update_post_meta($postId, '_rfmp_fromname_cancelled_customer', ($_POST['rfmp_fromname_cancelled_customer']));
        update_post_meta($postId, '_rfmp_fromemail_cancelled_customer', $_POST['rfmp_fromemail_cancelled_customer']);
        update_post_meta($postId, '_rfmp_enabled_chargedback_customer', ($_POST['rfmp_enabled_chargedback_customer']));
        update_post_meta($postId, '_rfmp_email_chargedback_customer', ($_POST['rfmp_email_chargedback_customer']));
        update_post_meta($postId, '_rfmp_subject_chargedback_customer', ($_POST['rfmp_subject_chargedback_customer']));
        update_post_meta($postId, '_rfmp_fromname_chargedback_customer', ($_POST['rfmp_fromname_chargedback_customer']));
        update_post_meta($postId, '_rfmp_fromemail_chargedback_customer', ($_POST['rfmp_fromemail_chargedback_customer']));

        update_post_meta($postId, '_rfmp_enabled_paid_merchant', ($_POST['rfmp_enabled_paid_merchant']));
        update_post_meta($postId, '_rfmp_email_paid_merchant', ($_POST['rfmp_email_paid_merchant']));
        update_post_meta($postId, '_rfmp_subject_paid_merchant', ($_POST['rfmp_subject_paid_merchant']));
        update_post_meta($postId, '_rfmp_fromname_paid_merchant', ($_POST['rfmp_fromname_paid_merchant']));
        update_post_meta($postId, '_rfmp_fromemail_paid_merchant', ($_POST['rfmp_fromemail_paid_merchant']));
        update_post_meta($postId, '_rfmp_toemail_paid_merchant', ($_POST['rfmp_toemail_paid_merchant']));
        update_post_meta($postId, '_rfmp_enabled_expired_merchant', ($_POST['rfmp_enabled_expired_merchant']));
        update_post_meta($postId, '_rfmp_email_expired_merchant', ($_POST['rfmp_email_expired_merchant']));
        update_post_meta($postId, '_rfmp_subject_expired_merchant', ($_POST['rfmp_subject_expired_merchant']));
        update_post_meta($postId, '_rfmp_fromname_expired_merchant', ($_POST['rfmp_fromname_expired_merchant']));
        update_post_meta($postId, '_rfmp_fromemail_expired_merchant', ($_POST['rfmp_fromemail_expired_merchant']));
        update_post_meta($postId, '_rfmp_toemail_expired_merchant', ($_POST['rfmp_toemail_expired_merchant']));
        update_post_meta($postId, '_rfmp_enabled_cancelled_merchant', ($_POST['rfmp_enabled_cancelled_merchant']));
        update_post_meta($postId, '_rfmp_email_cancelled_merchant', ($_POST['rfmp_email_cancelled_merchant']));
        update_post_meta($postId, '_rfmp_subject_cancelled_merchant', ($_POST['rfmp_subject_cancelled_merchant']));
        update_post_meta($postId, '_rfmp_fromname_cancelled_merchant', ($_POST['rfmp_fromname_cancelled_merchant']));
        update_post_meta($postId, '_rfmp_fromemail_cancelled_merchant', ($_POST['rfmp_fromemail_cancelled_merchant']));
        update_post_meta($postId, '_rfmp_toemail_cancelled_merchant', ($_POST['rfmp_toemail_cancelled_merchant']));
        update_post_meta($postId, '_rfmp_enabled_chargedback_merchant', ($_POST['rfmp_enabled_chargedback_merchant']));
        update_post_meta($postId, '_rfmp_email_chargedback_merchant', ($_POST['rfmp_email_chargedback_merchant']));
        update_post_meta($postId, '_rfmp_subject_chargedback_merchant', ($_POST['rfmp_subject_chargedback_merchant']));
        update_post_meta($postId, '_rfmp_fromname_chargedback_merchant', ($_POST['rfmp_fromname_chargedback_merchant']));
        update_post_meta($postId, '_rfmp_fromemail_chargedback_merchant', ($_POST['rfmp_fromemail_chargedback_merchant']));
        update_post_meta($postId, '_rfmp_toemail_chargedback_merchant', ($_POST['rfmp_toemail_chargedback_merchant']));

        // Price options
        $sortOrder = 0;
        foreach ($_POST['rfmp_priceoptions_new'] as $key => $new) {
            if ($new == '1') {
                // new row
                $this->db->insert($this->mollieForms->getPriceOptionsTable(), [
                        'post_id'         => $postId,
                        'description'     => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_desc'][$key])),
                        'price'           => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_price'][$key])) ?: null,
                        'price_type'      => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_pricetype'][$key])),
                        'vat'             => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_vat'][$key])) ?: null,
                        'frequency'       => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_frequency'][$key])),
                        'frequency_value' => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_frequencyval'][$key])) ?: null,
                        'times'           => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_times'][$key])) ?: null,
                        'stock'           => $_POST['rfmp_priceoptions_stock'][$key] != '' ? esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_stock'][$key])) : null,
                        'sort_order'      => $sortOrder,
                ]);
            } elseif ($new == '-1') {
                if (strstr($key, 'po-')) {
                    // delete row
                    $this->db->delete($this->mollieForms->getPriceOptionsTable(), [
                            'ID' => str_replace('po-', '', $key),
                    ]);
                }
            } else {
                // existing row
                $this->db->update($this->mollieForms->getPriceOptionsTable(), [
                        'description'     => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_desc'][$key])),
                        'price'           => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_price'][$key])) ?: null,
                        'price_type'      => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_pricetype'][$key])),
                        'vat'             => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_vat'][$key])) ?: null,
                        'frequency'       => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_frequency'][$key])),
                        'frequency_value' => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_frequencyval'][$key])) ?: null,
                        'times'           => esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_times'][$key])) ?: null,
                        'stock'           => $_POST['rfmp_priceoptions_stock'][$key] != '' ? esc_sql(sanitize_text_field($_POST['rfmp_priceoptions_stock'][$key])) : null,
                        'sort_order'      => $sortOrder,
                ], [
                        'ID' => str_replace('po-', '', $key),
                ]);
            }

            $sortOrder++;
        }

        // Discount Codes
        foreach ($_POST['rfmp_discount_id'] as $key => $id) {
            if ($id == 0) {
                if (empty($_POST['rfmp_discount_code'][$key])) {
                    continue;
                }

                // new code
                $this->db->insert($this->mollieForms->getDiscountCodesTable(), [
                        'post_id'       => $postId,
                        'discount_code' => esc_sql(sanitize_text_field($_POST['rfmp_discount_code'][$key])),
                        'discount_type' => esc_sql(sanitize_text_field($_POST['rfmp_discount_type'][$key])),
                        'discount'      => esc_sql(sanitize_text_field($_POST['rfmp_discount'][$key])),
                        'valid_from'    => esc_sql(sanitize_text_field($_POST['rfmp_discount_valid_from'][$key])),
                        'valid_until'   => esc_sql(sanitize_text_field($_POST['rfmp_discount_valid_until'][$key])),
                        'times_max'     => esc_sql(sanitize_text_field($_POST['rfmp_discount_times_max'][$key])),
                ]);
            } else {
                if (empty($_POST['rfmp_discount_code'][$key])) {
                    $this->db->delete($this->mollieForms->getDiscountCodesTable(), [
                            'ID' => (int)$id,
                    ]);

                    continue;
                }

                // existing code
                $this->db->update($this->mollieForms->getDiscountCodesTable(), [
                        'discount_code' => esc_sql(sanitize_text_field($_POST['rfmp_discount_code'][$key])),
                        'discount_type' => esc_sql(sanitize_text_field($_POST['rfmp_discount_type'][$key])),
                        'discount'      => esc_sql(sanitize_text_field($_POST['rfmp_discount'][$key])),
                        'valid_from'    => esc_sql(sanitize_text_field($_POST['rfmp_discount_valid_from'][$key])),
                        'valid_until'   => esc_sql(sanitize_text_field($_POST['rfmp_discount_valid_until'][$key])),
                        'times_max'     => esc_sql(sanitize_text_field($_POST['rfmp_discount_times_max'][$key])),
                ], [
                        'ID' => (int)$id,
                ]);
            }
        }
    }

    /**
     * Registrations page
     */
    public function pageRegistrations()
    {
        $table = new RegistrationsTable($this->mollieForms);
        $table->prepare_items();

        if (isset($_GET['post'])) {
            $post = get_post(sanitize_text_field($_GET['post']));
        }

        if (isset($_GET['msg'])) {
            switch ($_GET['msg']) {
                case 'delete-ok':
                    $msg = '<div class="updated notice"><p>' .
                           esc_html__('The registration is successful deleted', 'mollie-forms') . '</p></div>';
                    break;
            }

            echo esc_html($msg ?? '');
        }
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Registrations', 'mollie-forms');
                echo(isset($post) ? ' <small>(' . esc_html($post->post_title) . ')</small>' : ''); ?></h2>

            <form action="edit.php" style="float: right;">
                <?php wp_nonce_field( 'search-mollie-forms-registrations' ); ?>
                <input type="hidden" name="post_type" value="mollie-forms">
                <input type="hidden" name="page" value="registrations">
                <input type="hidden" name="post" value="<?php echo esc_attr(sanitize_text_field($_GET['post'] ?? '')); ?>">

                <input type="text"
                       name="search"
                       value="<?php echo esc_attr(sanitize_text_field($_GET['search'] ?? '')); ?>"
                       placeholder="<?php esc_html_e('Search') ?>">
                <input type="submit" class="button action" value="<?php esc_html_e('Search') ?>">
            </form>


            <?php $table->display(); ?>
        </div>
        <?php
    }

    /**
     * Registration page
     *
     * @return mixed
     * @throws Exception
     */
    public function pageRegistration()
    {
        if (!isset($_GET['view'])) {
            return esc_html__('Registration not found', 'mollie-forms');
        }

        $id = (int) $_GET['view'];

        $registration = $this->db->get_row($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id=%d", $id));
        if ($registration == null) {
            return esc_html__('Registration not found', 'mollie-forms');
        }

        // Delete registration
        if (isset($_GET['delete']) && check_admin_referer('delete-reg_' . $id)) {
            $this->db->delete($this->mollieForms->getRegistrationsTable(), [
                    'id' => $id,
            ]);

	        $this->db->delete($this->mollieForms->getRegistrationFieldsTable(), [
			        'registration_id' => $id,
	        ]);

	        $this->db->delete($this->mollieForms->getRegistrationPriceOptionsTable(), [
			        'registration_id' => $id,
	        ]);

	        $this->db->delete($this->mollieForms->getPaymentsTable(), [
			        'registration_id' => $id,
	        ]);

	        $this->db->delete($this->mollieForms->getSubscriptionsTable(), [
			        'registration_id' => $id,
	        ]);

            wp_redirect('?post_type=mollie-forms&page=registrations&msg=delete-ok');
            exit;
        }

        // subscriptions table fix
        if ($registration->subs_fix) {
            $subsTable = $this->mollieForms->getSubscriptionsTable();
        } else {
            $subsTable = $this->mollieForms->getCustomersTable();
        }

        try {
	        // Connect with Mollie
	        $apiKey = get_post_meta($registration->post_id, '_rfmp_api_key', true);
	        $mollie = new MollieApi($apiKey);

	        // Get all subscriptions
	        $allSubs = $mollie->all('customers/' . sanitize_text_field($registration->customer_id) . '/subscriptions');
	        foreach ($allSubs as $sub) {
		        $this->db->update($subsTable, [
			        'sub_status' => $sub->status,
		        ], [
			        'subscription_id' => $sub->id,
		        ]);
	        }
        } catch (Exception $e) {

        }

	    $vatSetting = get_post_meta($registration->post_id, '_rfmp_vat_setting', true);

        $fields        = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE registration_id=%d", $id));
        $subscriptions = $this->db->get_results($this->db->prepare("SELECT * FROM {$subsTable} WHERE registration_id=%d", $id));
        $payments      = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getPaymentsTable()} WHERE registration_id=%d", $id));
        $priceOptions  = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationPriceOptionsTable()} WHERE registration_id=%d", $id));

        // Cancel subscription
        if (isset($_GET['cancel']) && check_admin_referer('cancel-sub_' . esc_html(sanitize_text_field($_GET['cancel'])))) {
            try {
                $mollie->delete('customers/' . sanitize_text_field($registration->customer_id) . '/subscriptions/' . sanitize_text_field($_GET['cancel']));
                $this->db->update($subsTable, [
                        'sub_status' => 'cancelled',
                ], [
                        'subscription_id' => (int) sanitize_text_field($_GET['cancel']),
                ]);

                wp_redirect('?post_type=' . esc_html(sanitize_text_field($_REQUEST['post_type'])) . '&page=' . esc_html(sanitize_text_field($_REQUEST['page'])) . '&view=' . esc_html(sanitize_text_field($_REQUEST['view'])) . '&msg=cancel-ok');
            } catch (Exception $e) {
                echo '<div class="error notice">' . esc_html($e->getMessage()) . '</div>';
            }
        }

        // Refund payment
        if (isset($_GET['refund']) && check_admin_referer('refund-payment_' . esc_html(sanitize_text_field($_GET['refund'])))) {
            try {
                if (substr($_GET['refund'], 0, 3) == 'ord') {
                    $mollie->post('orders/' . esc_html(sanitize_text_field($_GET['refund'])) . '/refunds', [
                            'lines' => [],
                    ]);
                } else {
                    $payment = $mollie->get('payments/' . esc_html(sanitize_text_field($_GET['refund'])));
                    $mollie->post('payments/' . esc_html(sanitize_text_field($payment->id)) . '/refunds', [
                            'amount' => [
                                    'currency' => $payment->amount->currency,
                                    'value'    => $payment->amount->value,
                            ],
                    ]);
                }

                $this->db->update($this->mollieForms->getPaymentsTable(), [
                        'payment_status' => 'refunded',
                ], [
                        'payment_id' => esc_sql(sanitize_text_field($_GET['refund'])),
                ]);

                wp_redirect('?post_type=' . esc_html(sanitize_text_field($_REQUEST['post_type'])) . '&page=' . esc_html(sanitize_text_field($_REQUEST['page'])) . '&view=' .
                            esc_html(sanitize_text_field($_REQUEST['view'])) . '&msg=refund-ok');

            } catch (Exception $e) {
                wp_redirect('?post_type=' . esc_html(sanitize_text_field($_REQUEST['post_type'])) . '&page=' . esc_html(sanitize_text_field($_REQUEST['page'])) . '&view=' .
                            esc_html(sanitize_text_field($_REQUEST['view'])) . '&msg=refund-nok');
            }
        }

        if (isset($_GET['msg'])) {
            switch ($_GET['msg']) {
                case 'refund-ok':
                    $msg = '<div class="updated notice"><p>' .
                           esc_html__('The payment is successful refunded', 'mollie-forms') . '</p></div>';
                    break;
                case 'refund-nok':
                    $msg = '<div class="error notice"><p>' .
                           esc_html__('The payment can not be refunded', 'mollie-forms') . '</p></div>';
                    break;
                case 'cancel-ok':
                    $msg = '<div class="updated notice"><p>' .
                           esc_html__('The subscription is successful cancelled', 'mollie-forms') . '</p></div>';
                    break;
            }

            echo isset($msg) ? esc_html($msg) : '';
        }

        include $this->mollieForms->getDirPath() . 'templates/admin/registration.php';
    }

    /**
     * Addons page
     */
    public function pageAddons()
    {
        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Add-ons', 'mollie-forms'); ?></h2>

            <ul class="products">
                <li class="product">
                    <a href="https://wobbie.nl/downloads/mailchimp-for-mollie-forms/" target="_blank">
                        <h2><?php esc_html_e('Mailchimp', 'mollie-forms'); ?></h2>
                        <p><?php esc_html_e('Add people to your Mailchimp mailing list.', 'mollie-forms'); ?></p>
                    </a>
                </li>
            </ul>
        </div>

        <?php
    }

    /**
     * Generate export of registrations
     */
    public function exportRegistrations()
    {
	    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'export-mollie-forms-registrations')) {
		    return;
	    }

	    $postId = (int) sanitize_text_field($_GET['post']);

	    if (!current_user_can('edit_post', $postId)) {
		    return;
	    }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=registrations.csv');
        $output = fopen('php://output', 'w');

        $headers = [
                esc_html__('Registration ID', 'mollie-forms'),
                esc_html__('Status', 'mollie-forms'),
                esc_html__('Date', 'mollie-forms'),
                esc_html__('Time', 'mollie-forms'),
                esc_html__('Currency', 'mollie-forms'),
                esc_html__('Total', 'mollie-forms'),
                esc_html__('Description', 'mollie-forms'),
                esc_html__('Price options', 'mollie-forms'),
        ];

        // get all fields for headers
        $registration = $this->db->get_row($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE post_id=%d ORDER BY id DESC LIMIT 1", $postId));

		if ($registration === null) {
			die(esc_html__('No registrations found for this form', 'mollie-forms'));
		}

        $fields       = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE registration_id=%d", $registration->id));
        foreach ($fields as $field) {
            $headers[] = esc_html($field->field);
        }

        // put header in csv
        fputcsv($output, $headers);

        // make all registration rows
        $registrations = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE post_id=%d ORDER BY id DESC", $postId));
        foreach ($registrations as $registration) {
            $paymentsPaid = $this->db->get_var($this->db->prepare("SELECT COUNT(*) FROM {$this->mollieForms->getPaymentsTable()} WHERE payment_status='paid' AND registration_id=%d", $registration->id));

            $priceOptions = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationPriceOptionsTable()} WHERE registration_id=%d", $registration->id));
            $options      = [];
            foreach ($priceOptions as $priceOption) {
                $currency  = $priceOption->currency ?: 'EUR';
                $frequency = $priceOption->frequency_value . ' ' . $priceOption->frequency;
                $options[] = $priceOption->quantity . 'x ' . $priceOption->description . ' (' .
                             ($priceOption->currency ?: 'EUR') . ' ' .
                             number_format($priceOption->price, $this->helpers->getCurrencies($currency), '.', '') .
                             ' ' . $this->helpers->getFrequencyLabel($frequency) . ')';
            }

            $created_at = explode(' ', $registration->created_at);

            $rows = [
                    $registration->id,
                    $paymentsPaid ? __('Paid', 'mollie-forms') : __('Not paid', 'mollie-forms'),
                    $created_at[0],
                    isset($created_at[1]) ? $created_at[1] : '00:00:00',
                    $registration->currency ?: 'EUR',
                    $registration->total_price,
                    $registration->description,
                    implode(' | ', $options),
            ];

            $fields = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE registration_id=%d", $registration->id));
            foreach ($fields as $field) {
                if ($field->type == 'checkbox') {
                    if ($field->value == '') {
                        $rows[] = __('Yes', 'mollie-forms');
                    } else {
                        $rows[] = $field->value == '1' ? __('Yes', 'mollie-forms') : __('No', 'mollie-forms');
                    }
                } else {
	                $fieldValue = str_replace(["\r\n", "\n\r", "\n", "\r"], '', $field->value);

                    $rows[] = esc_html($fieldValue);
                }
            }

            // put row in csv
            fputcsv($output, $rows);
        }
    }

    public function duplicateForm()
    {
	    if (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])), 'duplicate-mollie-forms-form')) {
		    return;
	    }

        $postId = (int) sanitize_text_field($_GET['post']);

	    if (!current_user_can('edit_post', $postId)) {
		    return;
	    }

        $title   = get_the_title($postId);
        $oldPost = get_post($postId);
        $post    = [
                'post_title'    => $title,
                'post_status'   => 'publish',
                'post_type'     => $oldPost->post_type,
                'post_author'   => wp_get_current_user()->ID,
                'post_content'  => $oldPost->post_content,
                'post_excerpt'  => $oldPost->post_excerpt,
                'post_name'     => $oldPost->post_name,
                'post_parent'   => $oldPost->post_parent,
                'post_password' => $oldPost->post_password,
                'to_ping'       => $oldPost->to_ping,
                'menu_order'    => $oldPost->menu_order,
        ];

        $newPostId = wp_insert_post($post);

        $post_meta_infos = $this->db->get_results($this->db->prepare("SELECT meta_key, meta_value FROM {$this->db->postmeta} WHERE post_id=%d", $oldPost->ID));
        if (count($post_meta_infos) != 0) {
            $sql_query = "INSERT INTO {$this->db->postmeta} (post_id, meta_key, meta_value) ";
            foreach ($post_meta_infos as $meta_info) {
                $meta_key = $meta_info->meta_key;
                if ($meta_key == '_wp_old_slug') {
                    continue;
                }
                $meta_value      = addslashes($meta_info->meta_value);
                $sql_query_sel[] = "SELECT $newPostId, '$meta_key', '$meta_value'";
            }

            if (!empty($sql_query_sel)) {
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $this->db->query($sql_query);
            }
        }

        $priceOptions = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getPriceOptionsTable()} WHERE post_id=%d ORDER BY sort_order ASC", $oldPost->ID));
        foreach ($priceOptions as $priceOption) {
            $this->db->insert($this->mollieForms->getPriceOptionsTable(), [
                    'post_id'         => $newPostId,
                    'description'     => $priceOption->description,
                    'price'           => $priceOption->price,
                    'price_type'      => $priceOption->price_type,
                    'vat'             => $priceOption->vat,
                    'frequency'       => $priceOption->frequency,
                    'frequency_value' => $priceOption->frequency_value,
                    'times'           => $priceOption->times,
                    'stock'           => $priceOption->stock,
                    'sort_order'      => $priceOption->sort_order,
            ]);
        }

        wp_redirect(admin_url('post.php?action=edit&post=' . $newPostId));
        exit;
    }
}

<?php

namespace MollieForms;


class MollieForms
{

    /**
     * Plugin version number
     */
    const PLUGIN_VERSION = '2.8.1';

    /**
     * Webhook URL for Mollie
     */
    const WEBHOOK_URL = '?mollie-forms=true&post_id=';

    /**
     * Database table names
     */
    const TABLE_REGISTRATIONS              = 'mollie_forms_registrations';
    const TABLE_REGISTRATION_FIELDS        = 'mollie_forms_registration_fields';
    const TABLE_REGISTRATION_PRICE_OPTIONS = 'mollie_forms_registration_price_options';
    const TABLE_PAYMENTS                   = 'mollie_forms_payments';
    const TABLE_CUSTOMERS                  = 'mollie_forms_customers';
    const TABLE_SUBSCRIPTIONS              = 'mollie_forms_subscriptions';
    const TABLE_PRICE_OPTIONS              = 'mollie_forms_price_options';
    const TABLE_DISCOUNT_CODES             = 'mollie_forms_discount_codes';

	const DEFAULT_MINIMUM_RECAPTCHA_SCORE = 0.5;

    private $db;
    public  $baseName, $dirUrl, $dirPath;

    /**
     * MollieForms constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;

        $this->initPlugin();
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return self::PLUGIN_VERSION;
    }

    /**
     * @return string
     */
    public function getWebhookUrl()
    {
        return self::WEBHOOK_URL;
    }

    /**
     * Registrations table name
     *
     * @return string
     */
    public function getRegistrationsTable()
    {
        return $this->db->prefix . self::TABLE_REGISTRATIONS;
    }

    /**
     * Registration fields table name
     *
     * @return string
     */
    public function getRegistrationFieldsTable()
    {
        return $this->db->prefix . self::TABLE_REGISTRATION_FIELDS;
    }

    /**
     * Registration price options table name
     *
     * @return string
     */
    public function getRegistrationPriceOptionsTable()
    {
        return $this->db->prefix . self::TABLE_REGISTRATION_PRICE_OPTIONS;
    }

    /**
     * Discount codes table name
     *
     * @return string
     */
    public function getDiscountCodesTable()
    {
        return $this->db->prefix . self::TABLE_DISCOUNT_CODES;
    }

    /**
     * Payments table name
     *
     * @return string
     */
    public function getPaymentsTable()
    {
        return $this->db->prefix . self::TABLE_PAYMENTS;
    }

    /**
     * Customers table name
     *
     * @return string
     */
    public function getCustomersTable()
    {
        return $this->db->prefix . self::TABLE_CUSTOMERS;
    }

    /**
     * Subscriptions table name
     *
     * @return string
     */
    public function getSubscriptionsTable()
    {
        return $this->db->prefix . self::TABLE_SUBSCRIPTIONS;
    }

    /**
     * Price options table name
     *
     * @return string
     */
    public function getPriceOptionsTable()
    {
        return $this->db->prefix . self::TABLE_PRICE_OPTIONS;
    }

    /**
     * @return mixed
     */
    public function getBaseName()
    {
        return $this->baseName;
    }

    /**
     * @return mixed
     */
    public function getDirUrl()
    {
        return $this->dirUrl;
    }

    /**
     * @return mixed
     */
    public function getDirPath()
    {
        return $this->dirPath;
    }

    /**
     * @param $file
     */
    public function setPluginFile($file)
    {
        $this->baseName = plugin_basename($file);
        $this->dirUrl   = plugin_dir_url($file);
        $this->dirPath  = plugin_dir_path($file);
    }

    /**
     * Initialize plugin
     */
    public function initPlugin()
    {
        add_action('init', [$this, 'registerPostType'], 0);
    }

    /**
     * Register Wordpress Post Type
     */
    public function registerPostType()
    {
        $labels = [
            'name'                  => esc_html_x('Mollie Forms', 'Registration Forms General Name', 'mollie-forms'),
            'singular_name'         => esc_html_x('Mollie Form', 'Registration Form Singular Name', 'mollie-forms'),
            'menu_name'             => esc_html__('Mollie Forms', 'mollie-forms'),
            'name_admin_bar'        => esc_html__('Registration Form', 'mollie-forms'),
            'archives'              => esc_html__('Item Archives', 'mollie-forms'),
            'parent_item_colon'     => esc_html__('Parent Item:', 'mollie-forms'),
            'all_items'             => esc_html__('All Forms', 'mollie-forms'),
            'add_new_item'          => esc_html__('Add New Form', 'mollie-forms'),
            'add_new'               => esc_html__('Add New', 'mollie-forms'),
            'new_item'              => esc_html__('New Form', 'mollie-forms'),
            'edit_item'             => esc_html__('Edit Form', 'mollie-forms'),
            'update_item'           => esc_html__('Update Form', 'mollie-forms'),
            'view_item'             => esc_html__('View Form', 'mollie-forms'),
            'search_items'          => esc_html__('Search Form', 'mollie-forms'),
            'not_found'             => esc_html__('Not found', 'mollie-forms'),
            'not_found_in_trash'    => esc_html__('Not found in Trash', 'mollie-forms'),
            'featured_image'        => esc_html__('Featured Image', 'mollie-forms'),
            'set_featured_image'    => esc_html__('Set featured image', 'mollie-forms'),
            'remove_featured_image' => esc_html__('Remove featured image', 'mollie-forms'),
            'use_featured_image'    => esc_html__('Use as featured image', 'mollie-forms'),
            'insert_into_item'      => esc_html__('Insert into form', 'mollie-forms'),
            'uploaded_to_this_item' => esc_html__('Uploaded to this form', 'mollie-forms'),
            'items_list'            => esc_html__('Forms list', 'mollie-forms'),
            'items_list_navigation' => esc_html__('Forms list navigation', 'mollie-forms'),
            'filter_items_list'     => esc_html__('Filter forms list', 'mollie-forms'),
        ];
        $args   = [
            'label'              => esc_html__('Mollie Forms', 'mollie-forms'),
            'description'        => esc_html__('Mollie Forms', 'mollie-forms'),
            'labels'             => $labels,
            'supports'           => [],
            'taxonomies'         => [],
            'hierarchical'       => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 5,
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'can_export'         => true,
            'has_archive'        => false,
            'publicly_queryable' => true,
            'rewrite'            => false,
            'menu_icon'          => 'dashicons-list-view',
        ];

        ob_start();
        register_post_type('mollie-forms', $args);
    }

}

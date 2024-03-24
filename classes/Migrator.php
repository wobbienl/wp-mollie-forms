<?php

namespace MollieForms;


class Migrator
{

    private $db, $mollieForms;

    /**
     * Migrator constructor.
     *
     * @param MollieForms $plugin
     */
    public function __construct($plugin)
    {
        global $wpdb;

        $this->db          = $wpdb;
        $this->mollieForms = $plugin;
    }

    /**
     * Check if migration is needed
     *
     * @return bool
     */
    public function needsMigration()
    {
        if (get_option('rfmp_version') === false) {
            return false;
        }

        return true;
    }

    /**
     * Perform the migration
     */
    public function runMigration()
    {
        if ($this->needsMigration()) {
            $this->replaceVersion();
            $this->renameDatabaseTables();
            $this->renamePostType();
            $this->moveShippingCostsToPostMeta();
            $this->movePriceOptionsToDatabase();
            $this->addTotalsField();
            $this->addDiscountCodeField();
        }

        if (get_option('mollie-forms_version') < '2.5.0') {
            $this->addDiscountCodeField();
        }

        // run normal installation
        $this->updateDatabase();
    }

    /**
     * Replace old version option
     */
    private function replaceVersion()
    {
        add_option('mollie-forms_version', $this->mollieForms->getVersion());
        delete_option('rfmp_version');
    }

    /**
     * Rename database tables
     */
    private function renameDatabaseTables()
    {
        $this->db->query("RENAME TABLE " . $this->db->prefix . "rfmp_registrations TO " . $this->mollieForms->getRegistrationsTable());
        $this->db->query("RENAME TABLE " . $this->db->prefix . "rfmp_registration_fields TO " . $this->mollieForms->getRegistrationFieldsTable());
        $this->db->query("RENAME TABLE " . $this->db->prefix . "rfmp_payments TO " . $this->mollieForms->getPaymentsTable());
        $this->db->query("RENAME TABLE " . $this->db->prefix . "rfmp_customers TO " . $this->mollieForms->getCustomersTable());
        $this->db->query("RENAME TABLE " . $this->db->prefix . "rfmp_subscriptions TO " . $this->mollieForms->getSubscriptionsTable());
    }

    /**
     * Rename Wordpress Post Type
     */
    private function renamePostType()
    {
        $this->db->query("UPDATE " . $this->db->prefix . "posts SET post_type='mollie-forms' WHERE post_type='rfmp'");
    }

    /**
     * Move shipping costs to post meta
     */
    private function moveShippingCostsToPostMeta()
    {
        // insert all price options into the table
        foreach (get_posts(['numberposts' => -1, 'post_type' => 'mollie-forms']) as $post) {
            $shippingCosts = '';

            $shippingOptions = get_post_meta($post->ID, '_rfmp_priceoption_shipping', true);
            foreach ($shippingOptions as $key => $value) {
                if ($value) {
                    $shippingCosts = str_replace(',', '.', $value);
                }

                break;
            }

            update_post_meta($post->ID, '_rfmp_shipping_costs', $shippingCosts);
        }
    }

    /**
     * Move Wordpress options with Mollie Forms price
     * options to a new created database table
     */
    private function movePriceOptionsToDatabase()
    {
        // first update database
        $this->updateDatabase();

        // insert all price options into the table
        foreach (get_posts(['numberposts' => -1, 'post_type' => 'mollie-forms']) as $post) {
            $option_desc         = get_post_meta($post->ID, '_rfmp_priceoption_desc', true);
            $option_price        = get_post_meta($post->ID, '_rfmp_priceoption_price', true);
            $option_pricetype    = get_post_meta($post->ID, '_rfmp_priceoption_pricetype', true);
            $option_frequency    = get_post_meta($post->ID, '_rfmp_priceoption_frequency', true);
            $option_frequencyval = get_post_meta($post->ID, '_rfmp_priceoption_frequencyval', true);
            $option_times        = get_post_meta($post->ID, '_rfmp_priceoption_times', true);

            $sortOrder = 0;
            foreach ($option_desc as $key => $desc) {
                $this->db->insert($this->mollieForms->getPriceOptionsTable(), [
                    'post_id'         => sanitize_text_field($post->ID),
                    'description'     => sanitize_text_field($desc),
                    'price'           => sanitize_text_field($option_price[$key]),
                    'price_type'      => sanitize_text_field($option_pricetype[$key]),
                    'frequency'       => sanitize_text_field($option_frequency[$key]),
                    'frequency_value' => sanitize_text_field($option_frequencyval[$key]),
                    'times'           => sanitize_text_field($option_times[$key]),
                    'sort_order'      => sanitize_text_field($sortOrder),
                ]);

                $sortOrder++;
            }
        }
    }

    /**
     * Add total field to forms
     */
    private function addTotalsField()
    {
        // add totals field to all forms
        foreach (get_posts(['numberposts' => -1, 'post_type' => 'mollie-forms']) as $post) {
            $fields         = [];
            $labels         = [];
            $values         = [];
            $classes        = [];
            $required       = [];
            $field_type     = get_post_meta($post->ID, '_rfmp_fields_type', true);
            $field_label    = get_post_meta($post->ID, '_rfmp_fields_label', true);
            $field_value    = get_post_meta($post->ID, '_rfmp_fields_value', true);
            $field_class    = get_post_meta($post->ID, '_rfmp_fields_class', true);
            $field_required = get_post_meta($post->ID, '_rfmp_fields_required', true);
            foreach ($field_type as $key => $field) {
                if ($field == 'submit') {
                    $fields[]   = 'total';
                    $labels[]   = '';
                    $values[]   = '';
                    $classes[]  = '';
                    $required[] = 0;
                }

                $fields[]   = $field;
                $labels[]   = $field_label[$key];
                $values[]   = $field_value[$key];
                $classes[]  = $field_class[$key];
                $required[] = $field_required[$key];
            }

            update_post_meta($post->ID, '_rfmp_fields_type', $fields);
            update_post_meta($post->ID, '_rfmp_fields_label', $labels);
            update_post_meta($post->ID, '_rfmp_fields_value', $values);
            update_post_meta($post->ID, '_rfmp_fields_class', $classes);
            update_post_meta($post->ID, '_rfmp_fields_required', $required);
        }
    }

    /**
     * Add total field to forms
     */
    private function addDiscountCodeField()
    {
        // add totals field to all forms
        foreach (get_posts(['numberposts' => -1, 'post_type' => 'mollie-forms']) as $post) {
            $fields         = [];
            $labels         = [];
            $values         = [];
            $classes        = [];
            $required       = [];
            $field_type     = get_post_meta($post->ID, '_rfmp_fields_type', true);
            $field_label    = get_post_meta($post->ID, '_rfmp_fields_label', true);
            $field_value    = get_post_meta($post->ID, '_rfmp_fields_value', true);
            $field_class    = get_post_meta($post->ID, '_rfmp_fields_class', true);
            $field_required = get_post_meta($post->ID, '_rfmp_fields_required', true);
            foreach ($field_type as $key => $field) {
                if ($field == 'total') {
                    $fields[]   = 'discount_code';
                    $labels[]   = __('Discount code', 'mollie-forms');
                    $values[]   = '';
                    $classes[]  = '';
                    $required[] = 0;
                }

                $fields[]   = $field;
                $labels[]   = $field_label[$key];
                $values[]   = $field_value[$key];
                $classes[]  = $field_class[$key];
                $required[] = $field_required[$key];
            }

            update_post_meta($post->ID, '_rfmp_fields_type', $fields);
            update_post_meta($post->ID, '_rfmp_fields_label', $labels);
            update_post_meta($post->ID, '_rfmp_fields_value', $values);
            update_post_meta($post->ID, '_rfmp_fields_class', $classes);
            update_post_meta($post->ID, '_rfmp_fields_required', $required);
        }
    }

    /**
     * Update database tables
     */
    private function updateDatabase()
    {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sqlRegistrations = "CREATE TABLE {$this->mollieForms->getRegistrationsTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at        datetime NOT NULL,
            post_id           mediumint(9) NOT NULL,
            customer_id       varchar(45),
            subscription_id   varchar(45),
            currency          varchar(45) DEFAULT NULL,
            total_price       decimal(8,2),
            total_vat         decimal(8,2),
            vat_setting       varchar(45) DEFAULT NULL,
            price_frequency   varchar(45),
            number_of_times   mediumint(9),
            description       varchar(255),
            subs_fix          smallint(1) NOT NULL DEFAULT 0,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlRegistrations);

        $sqlRegistrationFields = "CREATE TABLE {$this->mollieForms->getRegistrationFieldsTable()} (
            registration_id   mediumint(9) NOT NULL,
            type              varchar(255),
            field             varchar(255),
            value             longtext
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlRegistrationFields);

        $sqlPayments = "CREATE TABLE {$this->mollieForms->getPaymentsTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at        datetime NOT NULL,
            registration_id   mediumint(9) NOT NULL,
            payment_id        varchar(45) NOT NULL,
            payment_method    varchar(255) NOT NULL,
            payment_mode      varchar(255) NOT NULL,
            payment_status    varchar(255) NOT NULL,
            currency          varchar(45) DEFAULT NULL,
            amount            decimal(8,2) NOT NULL,
            rfmp_id           varchar(255) NOT NULL,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlPayments);

        $sqlCustomers = "CREATE TABLE {$this->mollieForms->getCustomersTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at        datetime NOT NULL,
            post_id           mediumint(9) NOT NULL,
            customer_id       varchar(45) NOT NULL,
            name              varchar(255) NOT NULL,
            email             varchar(255) NOT NULL,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlCustomers);

        $sqlSubscriptions = "CREATE TABLE {$this->mollieForms->getSubscriptionsTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            registration_id   mediumint(9) NOT NULL,
            subscription_id   varchar(45) NOT NULL,
            customer_id       varchar(45) NOT NULL,
            sub_mode          varchar(45) NOT NULL,
            sub_currency      varchar(45) DEFAULT NULL,
            sub_amount        float(15) NOT NULL,
            sub_times         mediumint(9) NOT NULL,
            sub_interval      varchar(45) NOT NULL,
            sub_description   varchar(255) NOT NULL,
            sub_method        varchar(45) NOT NULL,
            sub_status        varchar(25) NOT NULL,
            created_at        datetime NOT NULL,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlSubscriptions);

        $sqlPriceOptions = "CREATE TABLE {$this->mollieForms->getPriceOptionsTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id           mediumint(9) NOT NULL,
            description       varchar(255) NOT NULL,
            price             decimal(8,2) DEFAULT NULL,
            price_type        varchar(255) ,
            vat               mediumint(9) DEFAULT NULL,
            frequency         varchar(255) ,
            frequency_value   varchar(255) ,
            times             mediumint(9) ,
            stock             mediumint(9) DEFAULT NULL,
            sort_order        mediumint(9) DEFAULT NULL,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlPriceOptions);

        $sqlRegistrationPriceOptions = "CREATE TABLE {$this->mollieForms->getRegistrationPriceOptionsTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id           mediumint(9) NOT NULL,
            registration_id   mediumint(9) NOT NULL,
            price_option_id   mediumint(9) DEFAULT NULL,
            description       varchar(255) NOT NULL,
            quantity          mediumint(9) NOT NULL,
            currency          varchar(255) DEFAULT NULL,
            price             decimal(8,2) DEFAULT NULL,
            price_type        varchar(255) ,
            vat               mediumint(9) DEFAULT NULL,
            frequency         varchar(255) ,
            frequency_value   varchar(255) ,
            times             mediumint(9) ,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlRegistrationPriceOptions);

        $sqlDiscountCodes = "CREATE TABLE {$this->mollieForms->getDiscountCodesTable()} (
            id                mediumint(9) NOT NULL AUTO_INCREMENT,
            post_id           mediumint(9) NOT NULL,
            discount_code     varchar(255) NOT NULL,
            discount_type     varchar(255) NOT NULL,
            discount          decimal(8,2) NOT NULL,
            valid_from        datetime DEFAULT NULL,
            valid_until       datetime DEFAULT NULL,
            times_max         mediumint(9) DEFAULT NULL,
            times_used        mediumint(9) DEFAULT 0,
            UNIQUE KEY id (id)
        ) {$this->db->get_charset_collate()};";
        dbDelta($sqlDiscountCodes);

        update_option('mollie-forms_version', $this->mollieForms->getVersion());
    }

}

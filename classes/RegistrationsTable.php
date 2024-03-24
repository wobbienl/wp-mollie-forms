<?php

namespace MollieForms;

class RegistrationsTable extends \WP_List_Table
{

    private $mollieForms, $helpers;

    /**
     * RegistrationsTable constructor.
     *
     * @param MollieForms $plugin
     */
    public function __construct($plugin)
    {
		parent::__construct();

        $this->mollieForms = $plugin;
        $this->helpers     = new Helpers();
        $this->screen      = get_current_screen();
    }

    /**
     * @return array
     */
    function get_columns()
    {
        $columns                        = [];
        $columns['created_at']          = esc_html__('Date/time', 'mollie-forms');
        $columns['post_id']             = esc_html__('Form', 'mollie-forms');
        $columns['customer']            = esc_html__('Customer', 'mollie-forms');
        $columns['total_price']         = esc_html__('Total price', 'mollie-forms');
        $columns['payment_status']      = esc_html__('Payment status', 'mollie-forms');
        $columns['subscription_status'] = esc_html__('Subscription status', 'mollie-forms');
        $columns['description']         = esc_html__('Description', 'mollie-forms');
        $columns['actions']             = '';

        return $columns;
    }

    /**
     * @param $item
     *
     * @return string
     */
    function column_actions($item)
    {
        $url_view   = 'edit.php?post_type=mollie-forms&page=registration&view=' . $item['id'];
        $url_delete = wp_nonce_url('edit.php?post_type=mollie-forms&page=registration&view=' . $item['id'] . '&delete=true', 'delete-reg_' . $item['id']);
        return sprintf('<a href="%s">' . esc_html__('View', 'mollie-forms') .
                       '</a> <a href="%s" style="color:#a00;" onclick="return confirm(\'' .
                       esc_html__('Are you sure?', 'mollie-forms') . '\');">' . esc_html__('Delete', 'mollie-forms') .
                       '</a>', $url_view, $url_delete);
    }

    /**
     *
     */
    function prepare_items()
    {
        global $wpdb;
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = [];
        $this->_column_headers = [$columns, $hidden, $sortable];

        if (isset($_GET['post'], $_GET['search']) && !empty($_GET['post']) && !empty($_GET['search'])) {
	        check_admin_referer( 'search-mollie-forms-registrations' );

            $registrations = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT r.* FROM {$this->mollieForms->getRegistrationsTable()} r LEFT JOIN {$this->mollieForms->getRegistrationFieldsTable()} rf ON rf.registration_id = r.id WHERE r.post_id=%d AND (r.description LIKE CONCAT('%', %s, '%') OR rf.value LIKE CONCAT('%', %s, '%')) GROUP BY r.id ORDER BY r.id DESC",
                    esc_sql(sanitize_text_field($_GET['post'])),
                    esc_sql(sanitize_text_field($_GET['search'])),
                    esc_sql(sanitize_text_field($_GET['search']))
                ),
                ARRAY_A
            );
        } elseif (isset($_GET['post']) && !empty($_GET['post'])) {
	        check_admin_referer( 'search-mollie-forms-registrations' );
	        $registrations = $wpdb->get_results(
		        $wpdb->prepare(
			        "SELECT r.* FROM {$this->mollieForms->getRegistrationsTable()} r WHERE r.post_id=%d GROUP BY r.id ORDER BY r.id DESC",
			        esc_sql(sanitize_text_field($_GET['post'])),
		        ),
		        ARRAY_A
	        );
        } elseif (isset($_GET['search']) && !empty($_GET['search'])) {
	        check_admin_referer( 'search-mollie-forms-registrations' );
	        $registrations = $wpdb->get_results(
		        $wpdb->prepare(
			        "SELECT r.* FROM {$this->mollieForms->getRegistrationsTable()} r LEFT JOIN {$this->mollieForms->getRegistrationFieldsTable()} rf ON rf.registration_id = r.id WHERE (r.description LIKE CONCAT('%', %s, '%') OR rf.value LIKE CONCAT('%', %s, '%')) GROUP BY r.id ORDER BY r.id DESC",
			        esc_sql(sanitize_text_field($_GET['search'])),
			        esc_sql(sanitize_text_field($_GET['search']))
		        ),
		        ARRAY_A
	        );
        } else {
	        $registrations = $wpdb->get_results(
                    "SELECT r.* FROM {$this->mollieForms->getRegistrationsTable()} r LEFT JOIN {$this->mollieForms->getRegistrationFieldsTable()} rf ON rf.registration_id = r.id GROUP BY r.id ORDER BY r.id DESC",
		        ARRAY_A
	        );
        }

        $per_page     = 25;
        $current_page = $this->get_pagenum();
        $total_items  = count($registrations);

        $d = array_slice($registrations, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args([
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil($total_items / $per_page),
        ]);
        $this->items = $d;
    }

    /**
     * @param $item
     * @param $column_name
     *
     * @return string
     */
    function column_default($item, $column_name)
    {
        global $wpdb;
        switch ($column_name) {
            case 'customer':
                $name = $wpdb->get_row($wpdb->prepare("SELECT value FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE type='name' AND registration_id=%d", $item['id']));
                return $name->value;
            case 'total_price':
                return $this->helpers->getCurrencySymbol($item['currency'] ?: 'EUR') . ' ' . number_format($item[$column_name], $this->helpers->getCurrencies($item['currency'] ?: 'EUR'), ',', '');
            case 'post_id':
                $post = get_post($item[$column_name]);
                return $post->post_title;
            case 'created_at':
                return wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($item[$column_name]));
            case 'price_frequency':
                return $this->frequency_label($item[$column_name]);
            case 'payment_status':
                $payments = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->mollieForms->getPaymentsTable()} WHERE payment_status='paid' AND registration_id=%d", $item['id']));
                return $payments ?
                        '<span style="color: green;">' . esc_html__('Paid', 'mollie-forms') . ' (' . $payments . 'x)</span>' :
                        '<span style="color: red;">' . esc_html__('Not paid', 'mollie-forms') . '</span>';
            case 'subscription_status':
                $reg = $wpdb->get_row($wpdb->prepare("SELECT subs_fix FROM {$this->mollieForms->getRegistrationsTable()} WHERE id=%d", $item['id']));
                if ($reg->subs_fix) {
                    $subsTable = $this->mollieForms->getSubscriptionsTable();
                } else {
                    $subsTable = $this->mollieForms->getCustomersTable();
                }

                $subscriptions = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$subsTable} WHERE sub_status='active' AND registration_id=%d", $item['id']));
                if ($item['price_frequency'] == 'once') {
                    return '';
                }

                return $subscriptions ?
                        '<span style="color: green;">' . esc_html__('Active', 'mollie-forms') . ' (' . $subscriptions .
                        'x)</span>' : '<span style="color: red;">' . esc_html__('Not active', 'mollie-forms') . '</span>';
            default:
                return $item[$column_name];
        }
    }

    /**
     * @param $which
     */
    public function display_tablenav($which)
    {
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">
            <?php $this->pagination($which); ?>
            <br class="clear"/>
        </div>
        <?php
    }
}

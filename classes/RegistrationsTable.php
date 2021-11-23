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
        $columns['created_at']          = __('Date/time', 'mollie-forms');
        $columns['post_id']             = __('Form', 'mollie-forms');
        $columns['customer']            = __('Customer', 'mollie-forms');
        $columns['total_price']         = __('Total price', 'mollie-forms');
        $columns['payment_status']      = __('Payment status', 'mollie-forms');
        $columns['subscription_status'] = __('Subscription status', 'mollie-forms');
        $columns['description']         = __('Description', 'mollie-forms');
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
        $url_delete = wp_nonce_url('edit.php?post_type=mollie-forms&page=registration&view=' . $item['id'] .
                                   '&delete=true', 'delete-reg_' . $item['id']);
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

        $where = [];
        if (isset($_GET['post']) && !empty($_GET['post'])) {
            $where[] = 'r.post_id="' . esc_sql($_GET['post']) . '"';
        }
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $where[] = '(r.description LIKE "%' . esc_sql($_GET['search']) . '%" OR rf.value LIKE "%' . esc_sql($_GET['search']) . '%")';
        }

        if (!empty($where)) {
            $where = " WHERE " . implode(' AND ', $where);
        } else {
            $where = '';
        }

        $registrations = $wpdb->get_results(
            "SELECT r.* FROM " . $this->mollieForms->getRegistrationsTable() . " r" .
            (isset($_GET['search']) ? " LEFT JOIN " . $this->mollieForms->getRegistrationFieldsTable() . " rf ON rf.registration_id = r.id" : "") .
            $where .
            " GROUP BY r.id ORDER BY r.id DESC",
            ARRAY_A
        );

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
                $name = $wpdb->get_row("SELECT value FROM {$this->mollieForms->getRegistrationFieldsTable()} WHERE type='name' AND registration_id=" .
                                       $item['id']);
                return $name->value;
                break;
            case 'total_price':
                return $this->helpers->getCurrencySymbol($item['currency'] ?: 'EUR') . ' ' .
                       number_format($item[$column_name], $this->helpers->getCurrencies($item['currency'] ?:
                               'EUR'), ',', '');
                break;
            case 'post_id':
                $post = get_post($item[$column_name]);
                return $post->post_title;
                break;
            case 'created_at':
                return date_i18n(get_option('date_format') . ' ' .
                                 get_option('time_format'), strtotime($item[$column_name]));
                break;
            case 'price_frequency':
                return $this->frequency_label($item[$column_name]);
                break;
            case 'payment_status':
                $payments = $wpdb->get_var("SELECT COUNT(*) FROM {$this->mollieForms->getPaymentsTable()} WHERE payment_status='paid' AND registration_id=" .
                                           (int) $item['id']);
                return $payments ?
                        '<span style="color: green;">' . __('Paid', 'mollie-forms') . ' (' . $payments . 'x)</span>' :
                        '<span style="color: red;">' . __('Not paid', 'mollie-forms') . '</span>';
                break;
            case 'subscription_status':
                $reg = $wpdb->get_row("SELECT subs_fix FROM {$this->mollieForms->getRegistrationsTable()} WHERE id=" .
                                      $item['id']);
                if ($reg->subs_fix) {
                    $subsTable = $this->mollieForms->getSubscriptionsTable();
                } else {
                    $subsTable = $this->mollieForms->getCustomersTable();
                }

                $subscriptions = $wpdb->get_var("SELECT COUNT(*) FROM " . $subsTable .
                                                " WHERE sub_status='active' AND registration_id=" . (int) $item['id']);
                if ($item['price_frequency'] == 'once') {
                    return '';
                }

                return $subscriptions ?
                        '<span style="color: green;">' . __('Active', 'mollie-forms') . ' (' . $subscriptions .
                        'x)</span>' : '<span style="color: red;">' . __('Not active', 'mollie-forms') . '</span>';
                break;
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
<?php

namespace MollieForms;


use DateTime;

class Email
{

    private $db, $mollieForms, $helpers;

    public function __construct($plugin)
    {
        global $wpdb;
        $this->db          = $wpdb;
        $this->mollieForms = $plugin;
        $this->helpers     = new Helpers();
    }

    /**
     * Send email to customer or merchant
     *
     * @param int    $post
     * @param string $status
     * @param int    $registrationId
     * @param object $payment
     * @param string $type 'customer' or 'merchant'
     */
    public function send($post, $status, $registrationId, $payment, $type)
    {
        $status = str_replace('canceled', 'cancelled', $status);
        if (!in_array($status, ['paid', 'expired', 'cancelled', 'charged_back'])) {
            return;
        }

        if (isset($payment->sequenceType) && $payment->sequenceType === 'recurring' && $status === 'paid') {
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
            wp_date(get_option('date_format') . ' ' . get_option('time_format'), $createdAt->getTimestamp() + $createdAt->getOffset()),
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

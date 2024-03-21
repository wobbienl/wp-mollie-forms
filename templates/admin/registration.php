<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">
    <h2><?php esc_html_e('Registration', 'mollie-forms');?></h2>

    <table class="wp-list-table widefat fixed striped rfmp_page_registration">
        <tbody id="the-list">
        <?php foreach ($fields as $row) { ?>
            <tr>
                <td class="field column-field column-primary"><strong><?php echo esc_html($row->field);?></strong></td>
                <?php if ($row->type == 'checkbox') { ?>
                    <?php if ($row->value == '') { ?>
                        <td class="value column-value"><?php esc_html_e('Yes', 'mollie-forms');?></td>
                    <?php } else { ?>
                        <td class="value column-value"><?php echo $row->value == '1' ? esc_html__('Yes', 'mollie-forms') : esc_html__('No', 'mollie-forms');?></td>
                    <?php } ?>
                <?php } elseif ($row->type === 'file') {
                    $file = wp_get_attachment_url($row->value);
                    ?>
                    <td class="value column-value"><a href="<?php echo nl2br(esc_html('upload.php?item=' . $row->value));?>" target="_blank"><?php echo nl2br(esc_html($file));?></a></td>
                <?php } else { ?>
                    <td class="value column-value"><?php echo nl2br(esc_html($row->value));?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        <tr>
            <td class="field column-field column-primary"><strong><?php esc_html_e('Total price', 'mollie-forms');?></strong></td>
            <td class="value column-value"><?php echo esc_html($this->helpers->getCurrencySymbol($registration->currency ?: 'EUR')) . ' ' . esc_html(number_format($registration->total_price, $this->helpers->getCurrencies($registration->currency ?: 'EUR'), ',', ''));?></td>
        </tr>
        <tr>
            <td class="field column-field column-primary"><strong><?php esc_html_e('Mollie Customer ID', 'mollie-forms');?></strong></td>
            <td class="value column-value"><?php echo esc_html($registration->customer_id);?></td>
        </tr>
        </tbody>
    </table><br>

    <?php if ($priceOptions !== null) { ?>
        <h3><?php esc_html_e('Price options', 'mollie-forms');?></h3>
        <table class="wp-list-table widefat fixed striped rfmp_page_registration_subscriptions">
            <thead>
            <tr>
                <th style="width: 50px"></th>
                <th><?php esc_html_e('Description', 'mollie-forms');?></th>
                <th><?php esc_html_e('Price', 'mollie-forms');?></th>
                <th><?php esc_html_e('VAT', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subtotal', 'mollie-forms');?></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php
            $currency   = $registration->currency ?: 'EUR';
            $totalPrice = 0.00;
            $totalVat   = 0.00;
            foreach ($priceOptions as $priceOption)
            {
                $frequency   = $priceOption->frequency == 'once' ? '' : esc_html($this->helpers->getFrequencyLabel($priceOption->frequency_value . ' ' . $priceOption->frequency));

                $optionPrice = $priceOption->price * $priceOption->quantity;
	            $optionVat   = ($priceOption->vat / 100) * $optionPrice;
                $totalPrice += $optionPrice;

                // add VAT to total if price is excl.
                if ($vatSetting == 'excl') {
	                $optionVat   = ($priceOption->vat / 100) * $optionPrice;

                    $totalPrice += $optionVat;
                    $optionPrice+= $optionVat;
                } else {
	                $optionVat   = ($priceOption->vat / (100 + $priceOption->vat)) * $optionPrice;
                }

	            $totalVat += $optionVat;

	            ?>
                <tr>
                    <td><?php echo esc_html($priceOption->quantity);?>x</td>
                    <td><?php echo esc_html($priceOption->description);?></td>
                    <td><?php echo esc_html($this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') . ' ' . number_format($priceOption->price, $this->helpers->getCurrencies($currency), ',', '') . ' ' . $frequency);?></td>
                    <td><?php echo esc_html($this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') . ' ' . number_format($optionVat, $this->helpers->getCurrencies($currency), ',', ''));?></td>
                    <td><?php echo esc_html($this->helpers->getCurrencySymbol($priceOption->currency ?: 'EUR') . ' ' . number_format($optionPrice, $this->helpers->getCurrencies($currency), ',', '') . ' ' . $frequency);?></td>
                </tr>
            <?php } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong><?php esc_html_e('Total', 'mollie-forms');?></strong></td>
                    <td><strong><?php echo esc_html($this->helpers->getCurrencySymbol($currency ?: 'EUR') . ' ' . number_format($totalVat, $this->helpers->getCurrencies($currency), ',', ''));?></strong></td>
                    <td><strong><?php echo esc_html($this->helpers->getCurrencySymbol($currency ?: 'EUR') . ' ' . number_format($totalPrice, $this->helpers->getCurrencies($currency), ',', ''));?></strong></td>
                </tr>
            </tfoot>
        </table><br>
    <?php } ?>

    <?php if ($registration->price_frequency != 'once' && $subscriptions != null) { ?>
        <h3><?php esc_html_e('Subscriptions', 'mollie-forms');?></h3>
        <table class="wp-list-table widefat fixed striped rfmp_page_registration_subscriptions">
            <thead>
            <tr>
                <th><?php esc_html_e('Subscription ID', 'mollie-forms');?></th>
                <th><?php esc_html_e('Created at', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subscription mode', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subscription amount', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subscription number of times', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subscription interval', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subscription description', 'mollie-forms');?></th>
                <th><?php esc_html_e('Subscription status', 'mollie-forms');?></th>
                <th></th>
            </tr>
            </thead>
            <tbody id="the-list">
            <?php
            foreach ($subscriptions as $subscription) {
                $url_cancel = wp_nonce_url('?post_type=mollie-forms&page=registration&view=' . $id . '&cancel=' . $subscription->subscription_id, 'cancel-sub_' . $subscription->subscription_id);
                ?>
                <tr>
                    <td class="column-subscription_id"><?php echo esc_html($subscription->subscription_id);?></td>
                    <td class="column-created_at"><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($subscription->created_at)));?></td>
                    <td class="column-sub_mode"><?php echo esc_html($subscription->sub_mode);?></td>
                    <td class="column-sub_amount"><?php echo esc_html($this->helpers->getCurrencySymbol($subscription->sub_currency ?: 'EUR') . ' ' . esc_html(number_format($subscription->sub_amount, $this->helpers->getCurrencies($subscription->sub_currency ?: 'EUR'), ',', '')));?></td>
                    <td class="column-sub_times"><?php echo esc_html($subscription->sub_times);?></td>
                    <td class="column-sub_interval"><?php echo esc_html($this->helpers->getFrequencyLabel($subscription->sub_interval));?></td>
                    <td class="column-sub_description"><?php echo esc_html($subscription->sub_description);?></td>
                    <td class="column-sub_status"><?php echo esc_html($subscription->sub_status);?></td>
                    <td class="column-cancel"><?php if ($subscription->sub_status == 'active') { ?><a href="<?php echo esc_url($url_cancel);?>" style="color:#a00;"><?php esc_html_e('Cancel', 'mollie-forms');?></a><?php } ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table><br>
    <?php } ?>

    <h3><?php esc_html_e('Payments', 'mollie-forms');?></h3>
    <table class="wp-list-table widefat fixed striped rfmp_page_registration_payments">
        <thead>
        <tr>
            <th><?php esc_html_e('ID', 'mollie-forms');?></th>
            <th><?php esc_html_e('Payment ID', 'mollie-forms');?></th>
            <th><?php esc_html_e('Created at', 'mollie-forms');?></th>
            <th><?php esc_html_e('Payment method', 'mollie-forms');?></th>
            <th><?php esc_html_e('Payment mode', 'mollie-forms');?></th>
            <th><?php esc_html_e('Payment status', 'mollie-forms');?></th>
            <th><?php esc_html_e('Amount', 'mollie-forms');?></th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody id="the-list">
        <?php
        foreach ($payments as $payment) {
            $url_refund = wp_nonce_url('?post_type=mollie-forms&page=registration&view=' . $id . '&refund=' . $payment->payment_id, 'refund-payment_' . $payment->payment_id);
            try {
                $mollie_payment = $mollie->get('payments/' . sanitize_text_field($payment->payment_id));
            } catch(Exception $e) {

            }
            ?>
            <tr>
                <td class="column-rfmp_id"><?php echo esc_html($payment->rfmp_id);?></td>
                <td class="column-payment_id"><?php echo esc_html($payment->payment_id);?></td>
                <td class="column-created_at"><?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payment->created_at)));?></td>
                <td class="column-payment_method"><?php echo esc_html($payment->payment_method);?></td>
                <td class="column-payment_mode"><?php echo esc_html($payment->payment_mode);?></td>
                <td class="column-payment_status"><?php echo esc_html($payment->payment_status);?></td>
                <td class="column-amount"><?php echo esc_html($this->helpers->getCurrencySymbol($payment->currency ?: 'EUR') . ' ' . number_format($payment->amount, $this->helpers->getCurrencies($payment->currency ?: 'EUR'), ',', ''));?></td>
                <td><?php echo (isset($mollie_payment, $mollie_payment->details->consumerName) ? esc_html($mollie_payment->details->consumerName) . '<br>' . esc_html($mollie_payment->details->consumerAccount) : '');?></td>
                <td class="column-cancel"><?php if ($payment->payment_status == 'paid') { ?><a href="<?php echo esc_url($url_refund);?>" style="color:#a00;"><?php esc_html_e('Refund', 'mollie-forms');?></a><?php } ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table><br>
</div>

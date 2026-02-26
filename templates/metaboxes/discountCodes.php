<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script id="rfmp_template_discountcode" type="text/template">
    <tr>
        <td></td>
        <td>
            <input type="hidden" name="rfmp_discount_id[]" value="0">
            <input type="text" name="rfmp_discount_code[]" class="rfmp_discount_code" value="">
        </td>
        <td>
            <select name="rfmp_discount_type[]" style="width: 95%;">
                <option value="amount"><?php esc_html_e('Amount', 'mollie-forms') ?></option>
                <option value="percentage"><?php esc_html_e('Percentage', 'mollie-forms') ?></option>
            </select>
        </td>
        <td>
            <input type="number" step="any" name="rfmp_discount[]" value="">
        </td>
        <td>
            <input type="datetime-local" name="rfmp_discount_valid_from[]" value="">
        </td>
        <td>
            <input type="datetime-local" name="rfmp_discount_valid_until[]" value="">
        </td>
        <td>
            <input type="number" name="rfmp_discount_times_max[]" value="" style="width: 70px;">
        </td>
        <td>
            <a href="javascript: void(0);"
               style="float: right; margin-right: 5px;"
               class="delete"><?php esc_html_e('Delete', 'mollie-forms'); ?></a>
        </td>
    </tr>
</script>

<div class='inside'>
    <table class="widefat rfmp_table" id="rfmp_discountcodes">
        <thead>
            <tr>
                <th></th>
                <th><?php esc_html_e('Discount code', 'mollie-forms'); ?></th>
                <th><?php esc_html_e('Type', 'mollie-forms'); ?></th>
                <th><?php esc_html_e('Discount', 'mollie-forms'); ?></th>
                <th><?php esc_html_e('Valid from', 'mollie-forms'); ?></th>
                <th><?php esc_html_e('Valid until', 'mollie-forms'); ?></th>
                <th>
                    <?php esc_html_e('Number of times', 'mollie-forms'); ?>
                    <a href="javascript: void(0);"
                       style="cursor: help;"
                       title="<?php esc_html_e('Leave empty for no limit', 'mollie-forms'); ?>">?</a>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($discountCodes as $discountCode) { ?>
                <tr>
                    <td>
                        <?php
                        $now = new DateTime('now', wp_timezone());
                        $validFrom = new DateTime($discountCode->valid_from, wp_timezone());
                        $validUntil = new DateTime($discountCode->valid_until, wp_timezone());
                        ?>

                        <?php if ($discountCode->times_max > 0 && $discountCode->times_used >= $discountCode->times_max) { ?>
                            <span title="<?php esc_html_e('All codes are used', 'mollie-forms'); ?>" style="border-radius: 10px; width: 10px; height: 10px; background: red; display: inline-block"></span>
                        <?php } elseif ($now->getTimestamp() > $validFrom->getTimestamp() && $now->getTimestamp() < $validUntil->getTimestamp()) { ?>
                            <span title="<?php esc_html_e('Currently active', 'mollie-forms'); ?>" style="border-radius: 10px; width: 10px; height: 10px; background: green; display: inline-block"></span>
                        <?php } elseif ($validFrom->getTimestamp() > $now->getTimestamp() && $now->getTimestamp() < $validUntil->getTimestamp()) { ?>
                            <span title="<?php esc_html_e('Not yet active', 'mollie-forms'); ?>" style="border-radius: 10px; width: 10px; height: 10px; background: orange; display: inline-block"></span>
                        <?php } else { ?>
                            <span title="<?php esc_html_e('Expired', 'mollie-forms'); ?>" style="border-radius: 10px; width: 10px; height: 10px; background: red; display: inline-block"></span>
                        <?php } ?>
                    </td>
                    <td>
                        <input type="hidden" name="rfmp_discount_id[]" value="<?php echo (int) $discountCode->id ?>">
                        <input type="text"
                               name="rfmp_discount_code[]"
                               value="<?php echo esc_attr($discountCode->discount_code) ?>">
                    </td>
                    <td>
                        <select name="rfmp_discount_type[]" style="width: 95%;">
                            <option value="amount"><?php esc_html_e('Amount', 'mollie-forms') ?></option>
                            <option value="percentage" <?php echo $discountCode->discount_type === 'percentage' ?
                                    'selected' : '' ?>><?php esc_html_e('Percentage', 'mollie-forms') ?></option>
                        </select>
                    </td>
                    <td>
                        <input type="number"
                               step="any"
                               name="rfmp_discount[]"
                               value="<?php echo esc_attr($discountCode->discount) ?>">
                    </td>
                    <td>
                        <input type="datetime-local" required
                               name="rfmp_discount_valid_from[]"
                               value="<?php echo esc_attr($validFrom->format('Y-m-d\TH:i')) ?>">
                    </td>
                    <td>
                        <input type="datetime-local" required
                               name="rfmp_discount_valid_until[]"
                               value="<?php echo esc_attr($validUntil->format('Y-m-d\TH:i')) ?>">
                    </td>
                    <td>
                        <?php echo esc_html($discountCode->times_used) ?> / <input type="number"
                                                                                   name="rfmp_discount_times_max[]"
                                                                                   value="<?php echo esc_attr($discountCode->times_max ?:
                                                                                           '') ?>"
                                                                                   style="width: 70px;">
                    </td>
                    <td>
                        <a href="javascript: void(0);"
                           style="float: right; margin-right: 5px;"
                           class="delete"><?php esc_html_e('Delete', 'mollie-forms'); ?></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="8">
                    <input type="button"
                           id="rfmp_add_discountcode"
                           class="button"
                           value="<?php esc_html_e('Add new discount code', 'mollie-forms'); ?>">
                </th>
            </tr>
        </tfoot>
    </table>
</div>

<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script id="rfmp_template_priceoption" type="text/template">
    <tr>
        <td class="sort"></td>
        <td><input type="text" name="rfmp_priceoptions_desc[]" style="width:100%;"></td>
        <td>
            <select name="rfmp_priceoptions_pricetype[]" class="rfmp_pricetype">
                <option value="fixed"><?php esc_html_e('Fixed', 'mollie-forms');?></option>
                <option value="open"><?php esc_html_e('Open', 'mollie-forms');?></option>
            </select>
            <input type="number" min="0.50" step="any" placeholder="<?php esc_html_e('Amount', 'mollie-forms');?>" data-ph-fixed="<?php esc_html_e('Amount', 'mollie-forms');?>" data-ph-open="<?php esc_html_e('Minimum amount', 'mollie-forms');?>" name="rfmp_priceoptions_price[]" style="width:70px">
        </td>
        <td>
            <input type="number" min="0" name="rfmp_priceoptions_vat[]" style="width: 50px;">
        </td>
        <td>
            <input type="number" min="0" name="rfmp_priceoptions_stock[]" style="width: 60px;">
        </td>
        <td>
            <input type="number" name="rfmp_priceoptions_frequencyval[]" style="width:50px;display:none;">
            <select name="rfmp_priceoptions_frequency[]" class="rfmp_frequency">
                <option value="once"><?php esc_html_e('Once', 'mollie-forms');?></option>
                <option value="months"><?php esc_html_e('Months', 'mollie-forms');?></option>
                <option value="weeks"><?php esc_html_e('Weeks', 'mollie-forms');?></option>
                <option value="days"><?php esc_html_e('Days', 'mollie-forms');?></option>
            </select>
        </td>
        <td>
            <input type="number" name="rfmp_priceoptions_times[]" style="width: 50px;display:none;">
        </td>
        <td width="1%">
            <a href="javascript: void(0);" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a>
            <input type="hidden" name="rfmp_priceoptions_new[]" class="rfmp_priceoptions_new" value="1">
        </td>
    </tr>
</script>

<div class='inside'>
    <table class="widefat rfmp_table" id="rfmp_priceoptions">
        <thead>
        <tr>
            <th class="sort"></th>
            <th><?php esc_html_e('Description', 'mollie-forms');?></th>
            <th><?php esc_html_e('Price', 'mollie-forms');?> <a href="#" style="cursor: help;" title="<?php esc_html_e('When the price type is set to open, this field is optional to set a minimum amount', 'mollie-forms');?>">?</a></th>
            <th><?php esc_html_e('VAT', 'mollie-forms');?> %</th>
            <th><?php esc_html_e('Stock', 'mollie-forms');?> <a href="#" style="cursor: help;" title="<?php esc_html_e('Leave empty if you don\'t want to activate stock management', 'mollie-forms');?>">?</a></th>
            <th><?php esc_html_e('Frequency', 'mollie-forms');?></th>
            <th><?php esc_html_e('Times', 'mollie-forms');?> <a href="#" style="cursor: help;" title="<?php esc_html_e('The number of times including the first payment. Leave empty or set to 0 for an on-going subscription', 'mollie-forms');?>">?</a></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($priceOptions as $priceOption) { ?>
            <tr>
                <td class="sort"></td>
                <td><input type="text" required style="width:100%;" name="rfmp_priceoptions_desc[po-<?php echo esc_attr($priceOption->id);?>]" value="<?php echo esc_attr($priceOption->description);?>"></td>
                <td>
                    <select name="rfmp_priceoptions_pricetype[po-<?php echo esc_attr($priceOption->id);?>]" class="rfmp_pricetype">
                        <option value="fixed"><?php esc_html_e('Fixed', 'mollie-forms');?></option>
                        <option value="open"<?php echo ($priceOption->price_type == 'open' ? ' selected' : '');?>><?php esc_html_e('Open', 'mollie-forms');?></option>
                    </select>
                    <input type="number" min="0.01" step="any" name="rfmp_priceoptions_price[po-<?php echo esc_attr($priceOption->id);?>]" value="<?php echo esc_attr($priceOption->price);?>" placeholder="<?php echo ($priceOption->price_type == 'open' ? esc_html__('Minimum amount', 'mollie-forms') : esc_html__('Amount', 'mollie-forms'));?>" style="width:70px">
                </td>
                <td>
                    <input type="number" min="0" name="rfmp_priceoptions_vat[po-<?php echo esc_attr($priceOption->id);?>]" value="<?php echo esc_attr($priceOption->vat);?>" style="width: 50px;">
                </td>
                <td>
                    <input type="number" min="0" name="rfmp_priceoptions_stock[po-<?php echo esc_attr($priceOption->id);?>]" value="<?php echo esc_attr($priceOption->stock);?>" style="width: 60px;">
                </td>
                <td>
                    <input type="number" name="rfmp_priceoptions_frequencyval[po-<?php echo esc_attr($priceOption->id);?>]" value="<?php echo esc_attr($priceOption->frequency_value);?>" style="width:50px;<?php echo ($priceOption->frequency == 'once' ? 'display:none;' : '');?>">
                    <select name="rfmp_priceoptions_frequency[po-<?php echo esc_attr($priceOption->id);?>]" class="rfmp_frequency">
                        <option value="once"><?php esc_html_e('Once', 'mollie-forms');?></option>
                        <option value="months"<?php echo ($priceOption->frequency == 'months' ? ' selected' : '');?>><?php esc_html_e('Months', 'mollie-forms');?></option>
                        <option value="weeks"<?php echo ($priceOption->frequency == 'weeks' ? ' selected' : '');?>><?php esc_html_e('Weeks', 'mollie-forms');?></option>
                        <option value="days"<?php echo ($priceOption->frequency == 'days' ? ' selected' : '');?>><?php esc_html_e('Days', 'mollie-forms');?></option>
                    </select>
                </td>
                <td>
                    <input type="number" name="rfmp_priceoptions_times[po-<?php echo esc_attr($priceOption->id);?>]" value="<?php echo esc_attr($priceOption->times);?>" style="width: 50px;<?php echo ($priceOption->frequency == 'once' ? 'display:none;' : '');?>">
                </td>
                <td width="1%">
                    <a href="javascript: void(0);" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a>
                    <input type="hidden" class="rfmp_priceoptions_new" name="rfmp_priceoptions_new[po-<?php echo esc_attr($priceOption->id);?>]" value="0">
                </td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="9"><input type="button" id="rfmp_add_priceoption" class="button" value="<?php esc_html_e('Add new price option', 'mollie-forms');?>"></th>
        </tr>
        </tfoot>
    </table>
</div>

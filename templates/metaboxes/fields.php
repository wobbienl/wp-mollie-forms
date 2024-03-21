<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script id="rfmp_template_field" type="text/template">
    <tr>
        <td class="sort"></td>
        <td>
            <select name="rfmp_fields_type[]" class="rfmp_type">
                <option value="text"><?php esc_html_e('Text field', 'mollie-forms');?></option>
                <option value="textarea"><?php esc_html_e('Text area', 'mollie-forms');?></option>
                <option value="dropdown"><?php esc_html_e('Dropdown', 'mollie-forms');?></option>
                <option value="checkbox"><?php esc_html_e('Checkbox', 'mollie-forms');?></option>
                <option value="radio"><?php esc_html_e('Radio buttons', 'mollie-forms');?></option>
                <option value="date"><?php esc_html_e('Date', 'mollie-forms');?></option>
                <option value="file"><?php esc_html_e('File', 'mollie-forms');?></option>
                <option value="text-only"><?php esc_html_e('Text', 'mollie-forms');?></option>
            </select>
        </td>
        <td><input type="text" name="rfmp_fields_label[]" style="width:100%"></td>
        <td><input style="display:none;width:100%" class="rfmp_value" type="text" name="rfmp_fields_value[]" placeholder="value1|value2|value3"></td>
        <td><input type="text" name="rfmp_fields_class[]" style="width:100%"></td>
        <td><input type="hidden" name="rfmp_fields_required[]" value="0"><input type="checkbox" name="rfmp_fields_required[]" value="1"></td>
        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
    </tr>
</script>

<div class='inside'>
    <table class="widefat rfmp_table" id="rfmp_fields">
        <thead>
        <tr>
            <th class="sort"></th>
            <th><?php esc_html_e('Type', 'mollie-forms');?></th>
            <th><?php esc_html_e('Label', 'mollie-forms');?></th>
            <th><?php esc_html_e('Values', 'mollie-forms');?></th>
            <th><?php esc_html_e('Class', 'mollie-forms');?></th>
            <th width="50"><?php esc_html_e('Required', 'mollie-forms');?></th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($field_type as $key => $type) { ?>
            <?php if ($type == 'priceoptions') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Price options', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="priceoptions"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'total') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Totals', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="total"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="hidden" name="rfmp_fields_class[]" value=""></td>
                    <td>
                        <select name="rfmp_fields_required[]" style="width: 100%;">
                            <option value="0"><?php esc_html_e('Hidden', 'mollie-forms');?></option>
                            <option value="1" <?php echo (isset($field_required[$key]) && $field_required[$key] == '1' ? 'selected' : '');?>><?php esc_html_e('Visible', 'mollie-forms');?></option>
                        </select>
                    </td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'discount_code') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Discount code', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="discount_code"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td>
                        <select name="rfmp_fields_required[]" style="width: 100%;">
                            <option value="0"><?php esc_html_e('Hidden', 'mollie-forms');?></option>
                            <option value="1" <?php echo (isset($field_required[$key]) && $field_required[$key] == '1' ? 'selected' : '');?>><?php esc_html_e('Visible', 'mollie-forms');?></option>
                        </select>
                    </td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'submit') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Submit button', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="submit"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'payment_methods') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Payment methods', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="payment_methods"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'name') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Name', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="name"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'email') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Email address', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="email"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'address') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Street and number', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="address"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } elseif ($type == 'postalCode') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Postal Code', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="postalCode"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } elseif ($type == 'city') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('City', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="city"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } elseif ($type == 'country') { ?>
                <tr>
                    <td class="sort"></td>
                    <td><?php esc_html_e('Country', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="country"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="checkbox" name="rfmp_fields_required[]" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } else { ?>
                <tr>
                    <td class="sort"></td>
                    <td>
                        <select name="rfmp_fields_type[]" class="rfmp_type">
                            <option value="text"><?php esc_html_e('Text field', 'mollie-forms');?></option>
                            <option value="textarea"<?php echo ($type == 'textarea' ? ' selected' : '');?>><?php esc_html_e('Text area', 'mollie-forms');?></option>
                            <option value="checkbox"<?php echo ($type == 'checkbox' ? ' selected' : '');?>><?php esc_html_e('Checkbox', 'mollie-forms');?></option>
                            <option value="dropdown"<?php echo ($type == 'dropdown' ? ' selected' : '');?>><?php esc_html_e('Dropdown', 'mollie-forms');?></option>
                            <option value="radio"<?php echo ($type == 'radio' ? ' selected' : '');?>><?php esc_html_e('Radio buttons', 'mollie-forms');?></option>
                            <option value="date"<?php echo ($type == 'date' ? ' selected' : '');?>><?php esc_html_e('Date', 'mollie-forms');?></option>
                            <option value="file"<?php echo ($type == 'file' ? ' selected' : '');?>><?php esc_html_e('File', 'mollie-forms');?></option>
                            <option value="text-only"<?php echo ($type == 'text-only' ? ' selected' : '');?>><?php esc_html_e('Text', 'mollie-forms');?></option>
                        </select>
                    </td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input style="<?php echo ($type != 'dropdown' && $type != 'radio' ? 'display:none;' : '');?>width:100%;" class="rfmp_value" type="text" name="rfmp_fields_value[]" value="<?php echo esc_attr($field_value[$key]);?>" placeholder="value1|value2|value3"></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_required[]" value="0"><input type="checkbox" value="1" name="rfmp_fields_required[<?php echo esc_attr($key);?>]"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>></td>
                    <td width="1%"><a href="javascript: void(0);" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="7"><input type="button" id="rfmp_add_field" class="button" value="<?php esc_html_e('Add new field', 'mollie-forms');?>"></th>
        </tr>
        </tfoot>
    </table>
</div>

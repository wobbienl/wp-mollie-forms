<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<script id="rfmp_template_field_countries" type="text/template">
    <button type="button" class="button rfmp_countries_button"></button>
</script>

<script id="rfmp_template_field" type="text/template">
    <tr>
        <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value=""></td>
        <td>
            <select name="rfmp_fields_type[]" class="rfmp_type">
                <option value="text"><?php esc_html_e('Text field', 'mollie-forms');?></option>
                <option value="textarea"><?php esc_html_e('Text area', 'mollie-forms');?></option>
                <option value="dropdown"><?php esc_html_e('Dropdown', 'mollie-forms');?></option>
                <option value="checkbox"><?php esc_html_e('Checkbox', 'mollie-forms');?></option>
                <option value="radio"><?php esc_html_e('Radio buttons', 'mollie-forms');?></option>
                <option value="date"><?php esc_html_e('Date', 'mollie-forms');?></option>
                <option value="file"><?php esc_html_e('File', 'mollie-forms');?></option>
                <option value="confirm"><?php esc_html_e('Confirm field', 'mollie-forms');?></option>
                <option value="text-only"><?php esc_html_e('Text', 'mollie-forms');?></option>
                <option value="country"><?php esc_html_e('Country', 'mollie-forms');?></option>
            </select>
        </td>
        <td><input type="text" name="rfmp_fields_label[]" style="width:100%"></td>
        <td><input style="display:none;width:100%" class="rfmp_value" type="text" name="rfmp_fields_value[]" placeholder="value1|value2|value3"></td>
        <td><input type="text" name="rfmp_fields_class[]" style="width:100%"></td>
        <td><input type="hidden" name="rfmp_fields_required[]" value="0" class="rfmp_required_value"><input type="checkbox" class="rfmp_required" value="1"></td>
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
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Price options', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="priceoptions"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'total') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
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
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
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
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Submit button', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="submit"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'payment_methods') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Payment methods', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="payment_methods"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'name') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Name', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="name"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'email') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Email address', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="email"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                    <td width="1%"></td>
                </tr>
            <?php } elseif ($type == 'address') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Street and number', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="address"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="hidden" name="rfmp_fields_required[]" value="<?php echo (isset($field_required[$key]) && $field_required[$key] ? '1' : '0');?>" class="rfmp_required_value"><input type="checkbox" class="rfmp_required" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } elseif ($type == 'postalCode') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Postal Code', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="postalCode"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="hidden" name="rfmp_fields_required[]" value="<?php echo (isset($field_required[$key]) && $field_required[$key] ? '1' : '0');?>" class="rfmp_required_value"><input type="checkbox" class="rfmp_required" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } elseif ($type == 'city') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('City', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="city"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_value[]" value=""></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="hidden" name="rfmp_fields_required[]" value="<?php echo (isset($field_required[$key]) && $field_required[$key] ? '1' : '0');?>" class="rfmp_required_value"><input type="checkbox" class="rfmp_required" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } elseif ($type == 'country') { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td><?php esc_html_e('Country', 'mollie-forms');?><input type="hidden" name="rfmp_fields_type[]" value="country"></td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td>
                        <input type="hidden" name="rfmp_fields_value[]" value="<?php echo esc_attr(isset($field_value[$key]) ? $field_value[$key] : '');?>">
                        <button type="button" class="button rfmp_countries_button"></button>
                    </td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <?php if ($api_type == 'orders') { ?>
                        <td><input type="checkbox" value="1" disabled checked><input type="hidden" name="rfmp_fields_required[]" value="1"></td>
                        <td width="1%"></td>
                    <?php } else { ?>
                        <td><input type="hidden" name="rfmp_fields_required[]" value="<?php echo (isset($field_required[$key]) && $field_required[$key] ? '1' : '0');?>" class="rfmp_required_value"><input type="checkbox" class="rfmp_required" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>></td>
                        <td width="1%"><a href="#" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    <?php } ?>
                </tr>
            <?php } else { ?>
                <tr>
                    <td class="sort"><input type="hidden" name="rfmp_fields_id[]" value="<?php echo esc_attr(isset($field_id[$key]) ? $field_id[$key] : '');?>"></td>
                    <td>
                        <select name="rfmp_fields_type[]" class="rfmp_type">
                            <option value="text"><?php esc_html_e('Text field', 'mollie-forms');?></option>
                            <option value="textarea"<?php echo ($type == 'textarea' ? ' selected' : '');?>><?php esc_html_e('Text area', 'mollie-forms');?></option>
                            <option value="checkbox"<?php echo ($type == 'checkbox' ? ' selected' : '');?>><?php esc_html_e('Checkbox', 'mollie-forms');?></option>
                            <option value="dropdown"<?php echo ($type == 'dropdown' ? ' selected' : '');?>><?php esc_html_e('Dropdown', 'mollie-forms');?></option>
                            <option value="radio"<?php echo ($type == 'radio' ? ' selected' : '');?>><?php esc_html_e('Radio buttons', 'mollie-forms');?></option>
                            <option value="date"<?php echo ($type == 'date' ? ' selected' : '');?>><?php esc_html_e('Date', 'mollie-forms');?></option>
                            <option value="file"<?php echo ($type == 'file' ? ' selected' : '');?>><?php esc_html_e('File', 'mollie-forms');?></option>
                            <option value="confirm"<?php echo ($type == 'confirm' ? ' selected' : '');?>><?php esc_html_e('Confirm field', 'mollie-forms');?></option>
                            <option value="text-only"<?php echo ($type == 'text-only' ? ' selected' : '');?>><?php esc_html_e('Text', 'mollie-forms');?></option>
                            <option value="country"><?php esc_html_e('Country', 'mollie-forms');?></option>
                        </select>
                    </td>
                    <td><input type="text" name="rfmp_fields_label[]" value="<?php echo esc_attr(isset($field_label[$key]) ? $field_label[$key] : '');?>" style="width:100%"></td>
                    <td><input style="<?php echo ($type != 'dropdown' && $type != 'radio' && $type != 'confirm' ? 'display:none;' : '');?>width:100%;" class="rfmp_value" type="text" name="rfmp_fields_value[]" value="<?php echo esc_attr($field_value[$key]);?>" placeholder="<?php echo ($type == 'confirm' ? esc_attr__('Label of field to confirm', 'mollie-forms') : 'value1|value2|value3');?>"></td>
                    <td><input type="text" name="rfmp_fields_class[]" value="<?php echo esc_attr(isset($field_class[$key]) ? $field_class[$key] : '');?>" style="width:100%"></td>
                    <td><input type="hidden" name="rfmp_fields_required[]" value="<?php echo (isset($field_required[$key]) && $field_required[$key] ? '1' : '0');?>" class="rfmp_required_value"><input type="checkbox" class="rfmp_required" value="1"<?php echo (isset($field_required[$key]) && $field_required[$key] ? ' checked' : '');?>></td>
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

    <div id="rfmp_countries_modal" class="rfmp-modal" style="display: none;">
        <div class="rfmp-modal-backdrop"></div>
        <div class="rfmp-modal-box" role="dialog" aria-modal="true" aria-labelledby="rfmp_countries_modal_title">
            <div class="rfmp-modal-header">
                <h2 id="rfmp_countries_modal_title"><?php esc_html_e('Exclude countries', 'mollie-forms');?></h2>
                <button type="button" class="rfmp-modal-close" aria-label="<?php esc_attr_e('Close', 'mollie-forms');?>">&times;</button>
            </div>
            <div class="rfmp-modal-tools">
                <p class="description"><?php esc_html_e('The countries you tick are not shown in this field.', 'mollie-forms');?></p>
                <p>
                    <input type="search" id="rfmp_countries_search" style="width: 100%;" placeholder="<?php esc_attr_e('Search country', 'mollie-forms');?>">
                </p>
                <p class="rfmp-modal-actions">
                    <a href="javascript: void(0);" class="rfmp_countries_all"><?php esc_html_e('Select all', 'mollie-forms');?></a> |
                    <a href="javascript: void(0);" class="rfmp_countries_none"><?php esc_html_e('Deselect all', 'mollie-forms');?></a>
                    <span class="rfmp-modal-count"></span>
                </p>
            </div>
            <div class="rfmp-modal-body">
                <div class="rfmp-modal-list">
                    <?php foreach ($this->helpers->getCountries() as $code => $country) { ?>
                        <label class="rfmp-modal-country">
                            <input type="checkbox" value="<?php echo esc_attr($code);?>"> <?php echo esc_html($country);?>
                        </label>
                    <?php } ?>
                </div>
                <p class="rfmp-modal-empty" style="display: none;"><?php esc_html_e('No countries found', 'mollie-forms');?></p>
            </div>
            <div class="rfmp-modal-footer">
                <button type="button" class="button rfmp_countries_cancel"><?php esc_html_e('Cancel', 'mollie-forms');?></button>
                <button type="button" class="button button-primary rfmp_countries_save"><?php esc_html_e('Save', 'mollie-forms');?></button>
            </div>
        </div>
    </div>
</div>

<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class='inside'>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_shortcode"><?php esc_html_e('Shortcode', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input id="rfmp_shortcode" value='[mollie-forms id="<?php echo esc_attr($post->ID);?>"]' readonly type="text" style="width: 350px" onfocus="this.select();"><br>
                <small><?php echo esc_html_e('Place this shortcode on a page or in a post', 'mollie-forms');?></small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_shortcode_total"><?php esc_html_e('Shortcode amount total raised', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input id="rfmp_shortcode_total" value='[mollie-forms-total id="<?php echo esc_attr($post->ID);?>"]' readonly type="text" style="width: 350px" onfocus="this.select();"><br>
                <small><?php echo esc_html_e('Place this shortcode on a page or in a post', 'mollie-forms');?></small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_api_key"><?php esc_html_e('Mollie API-key', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_api_key" id="rfmp_api_key" value="<?php echo esc_attr($api_key);?>" required type="text" style="width: 350px">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_api_type"><?php esc_html_e('Mollie API type', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_api_type" id="rfmp_api_type" style="width: 350px;">
                    <option value="payments"><?php esc_html_e('Payments', 'mollie-forms');?> (<?php esc_html_e('Klarna is not possible, VAT optional', 'mollie-forms');?>)</option>
                    <option value="orders" <?php echo ($api_type == 'orders' ? ' selected' : '');?>><?php esc_html_e('Orders', 'mollie-forms');?> (<?php esc_html_e('Klarna is possible, VAT required', 'mollie-forms');?>)</option>
                </select><br>
                <small><?php echo esc_html_e('Save the form after changing this setting.', 'mollie-forms');?></small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_currency"><?php esc_html_e('Currency', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_currency" id="rfmp_currency" style="width: 350px;">
                    <?php foreach ($this->helpers->getCurrencies() as $c => $d): ?>
                        <option value="<?php echo esc_attr($c);?>" <?php echo ($c == $currency ? 'selected' : '');?>><?php echo esc_html($c);?></option>
                    <?php endforeach;?>
                </select><br>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_locale"><?php esc_html_e('Language Mollie payment screen', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_locale" id="rfmp_locale" style="width: 350px;">
                    <?php foreach ($this->helpers->getLocales() as $l => $desc): ?>
                        <option value="<?php echo esc_attr($l);?>" <?php echo ($locale == $l ? 'selected' : '');?>><?php echo esc_html($desc);?></option>
                    <?php endforeach;?>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_label_display"><?php esc_html_e('Label display', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_label_display" style="width: 350px;">
                    <option value="label"><?php esc_html_e('Label', 'mollie-forms');?></option>
                    <option value="placeholder"<?php echo ($display_label == 'placeholder' ? ' selected' : '');?>><?php esc_html_e('Placeholder', 'mollie-forms');?></option>
                    <option value="both"<?php echo ($display_label == 'both' ? ' selected' : '');?>><?php esc_html_e('Both', 'mollie-forms');?></option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_priceoptions_display"><?php esc_html_e('Price options display', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_priceoptions_display" style="width: 350px;">
                    <option value="dropdown"><?php esc_html_e('Dropdown', 'mollie-forms');?></option>
                    <option value="list"<?php echo ($display_po == 'list' ? ' selected' : '');?>><?php esc_html_e('List', 'mollie-forms');?></option>
                    <option value="table_quantity"<?php echo ($display_po == 'table_quantity' ? ' selected' : '');?>><?php esc_html_e('Table with quantity fields (multiple options possible)', 'mollie-forms');?></option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_payment_methods_display"><?php esc_html_e('Payment methods display', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_payment_methods_display" style="width: 350px;">
                    <option value="dropdown"><?php esc_html_e('Dropdown', 'mollie-forms');?></option>
                    <option value="list"<?php echo ($display_pm == 'list' ? ' selected' : '');?>><?php esc_html_e('List with icons and text', 'mollie-forms');?></option>
                    <option value="text"<?php echo ($display_pm == 'text' ? ' selected' : '');?>><?php esc_html_e('List with text', 'mollie-forms');?></option>
                    <option value="icons"<?php echo ($display_pm == 'icons' ? ' selected' : '');?>><?php esc_html_e('List with icons', 'mollie-forms');?></option>
                </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_payment_desc"><?php esc_html_e('Payment description', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_payment_description" id="rfmp_payment_desc" value="<?php echo esc_attr($payment_description);?>" required type="text" style="width: 350px"><br>
                <small>
                    <?php esc_html_e('You can use variables in the payment description.', 'mollie-forms');?><br>
                    <?php esc_html_e('Visit our Help Center to learn more about variables:', 'mollie-forms');?> <a target="_blank" href="https://support.wobbie.nl/nl/veelgestelde-vragen/welke-variabelen-kan-ik-gebruiken">support.wobbie.nl</a>
                </small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_shipping_costs"><?php esc_html_e('Shipping costs', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_shipping_costs" min="0" id="rfmp_shipping_costs" value="<?php echo esc_attr($shippingCosts);?>" type="number" step="any" style="width: 350px"><br>
                <small><?php esc_html_e('Default shipping costs, used for every country without its own shipping costs. Leave empty if you don\'t want to charge shipping costs', 'mollie-forms');?></small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_shipping_country_field"><?php esc_html_e('Country field for shipping costs', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_shipping_country_field" id="rfmp_shipping_country_field" style="width: 350px;">
                    <option value=""><?php esc_html_e('First country field of the form', 'mollie-forms');?></option>
                    <?php foreach ($countryFields as $fieldId => $fieldLabel): ?>
                        <option value="<?php echo esc_attr($fieldId);?>" <?php echo ($countryField == $fieldId ? 'selected' : '');?>><?php echo esc_html($fieldLabel);?></option>
                    <?php endforeach;?>
                </select><br>
                <small><?php esc_html_e('The country field that determines the shipping costs. Add a country field in the "Form fields" box below.', 'mollie-forms');?></small>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label><?php esc_html_e('Shipping costs per country', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <?php
                $countryOptions = '';
                foreach ($this->helpers->getCountries() as $countryCode => $countryName) {
                    $countryOptions .= '<option value="' . esc_attr($countryCode) . '">' . esc_html($countryName) . '</option>';
                }
                ?>

                <script id="rfmp_template_shipping_country_cost" type="text/template">
                    <tr>
                        <td>
                            <select name="rfmp_shipping_country_costs_country[]" style="width: 100%;">
                                <?php echo $countryOptions;?>
                            </select>
                        </td>
                        <td>
                            <input type="number" min="0" step="any" name="rfmp_shipping_country_costs_price[]" value="" style="width: 100%;">
                        </td>
                        <td width="1%"><a href="javascript: void(0);" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                    </tr>
                </script>

                <table class="widefat rfmp_table" id="rfmp_shipping_country_costs" style="width: 350px;">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Country', 'mollie-forms');?></th>
                        <th><?php esc_html_e('Shipping costs', 'mollie-forms');?></th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($countryCosts as $countryCode => $countryPrice): ?>
                        <tr>
                            <td>
                                <select name="rfmp_shipping_country_costs_country[]" style="width: 100%;">
                                    <?php foreach ($this->helpers->getCountries() as $code => $country): ?>
                                        <option value="<?php echo esc_attr($code);?>" <?php echo ($code == $countryCode ? 'selected' : '');?>><?php echo esc_html($country);?></option>
                                    <?php endforeach;?>
                                </select>
                            </td>
                            <td>
                                <input type="number" min="0" step="any" name="rfmp_shipping_country_costs_price[]" value="<?php echo esc_attr($countryPrice);?>" style="width: 100%;">
                            </td>
                            <td width="1%"><a href="javascript: void(0);" class="delete"><?php esc_html_e('Delete', 'mollie-forms');?></a></td>
                        </tr>
                    <?php endforeach;?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th colspan="3">
                            <input type="button" id="rfmp_add_shipping_country_cost" class="button" value="<?php esc_html_e('Add country', 'mollie-forms');?>">
                        </th>
                    </tr>
                    </tfoot>
                </table>
                <small><?php esc_html_e('These shipping costs override the default shipping costs for the selected countries.', 'mollie-forms');?></small>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label><?php esc_html_e('VAT', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_vat_setting" style="width: 350px;">
                    <option value="incl"><?php esc_html_e('Prices are including VAT', 'mollie-forms');?></option>
                    <option value="excl"<?php echo ($vatSetting == 'excl' ? ' selected' : '');?>><?php esc_html_e('Prices are excluding VAT', 'mollie-forms');?></option>
                </select>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_payment_methods_display"><?php esc_html_e('After payment', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_after_payment" style="width: 350px;">
                    <option value="message"><?php esc_html_e('Show a message', 'mollie-forms');?></option>
                    <option value="redirect"<?php echo ($after_payment == 'redirect' ? ' selected' : '');?>><?php esc_html_e('Redirect to a page', 'mollie-forms');?></option>
                </select>
            </td>
        </tr>

        <tr valign="top" class="rfmp_after_payment_message" <?php echo $after_payment != 'redirect' ? '' : 'style="display: none;"';?>>
            <th scope="row" class="titledesc">
                <label for="rfmp_msg_success"><?php esc_html_e('Success message', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_msg_success" id="rfmp_msg_success" value="<?php echo esc_attr($message_success);?>" type="text" style="width: 350px">
            </td>
        </tr>
        <tr valign="top" class="rfmp_after_payment_message" <?php echo $after_payment != 'redirect' ? '' : 'style="display: none;"';?>>
            <th scope="row" class="titledesc">
                <label for="rfmp_msg_error"><?php esc_html_e('Error message', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_msg_error" id="rfmp_msg_error" value="<?php echo esc_attr($message_error);?>" type="text" style="width: 350px">
            </td>
        </tr>
        <tr valign="top" class="rfmp_after_payment_message" <?php echo $after_payment != 'redirect' ? '' : 'style="display: none;"';?>>
            <th scope="row" class="titledesc">
                <label for="rfmp_class_success"><?php esc_html_e('Class success message', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_class_success" id="rfmp_class_success" value="<?php echo esc_attr($class_success);?>" type="text" style="width: 350px">
            </td>
        </tr>
        <tr valign="top" class="rfmp_after_payment_message" <?php echo $after_payment != 'redirect' ? '' : 'style="display: none;"';?>>
            <th scope="row" class="titledesc">
                <label for="rfmp_class_error"><?php esc_html_e('Class error message', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_class_error" id="rfmp_class_error" value="<?php echo esc_attr($class_error);?>" type="text" style="width: 350px">
            </td>
        </tr>

        <tr valign="top" class="rfmp_after_payment_redirect" <?php echo $after_payment == 'redirect' ? '' : 'style="display: none;"';?>>
            <th scope="row" class="titledesc">
                <label for="rfmp_redirect_success"><?php esc_html_e('Redirect URL successful payment', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_redirect_success" id="rfmp_redirect_success" value="<?php echo esc_attr($redirect_success);?>" type="text" style="width: 350px">
            </td>
        </tr>
        <tr valign="top" class="rfmp_after_payment_redirect" <?php echo $after_payment == 'redirect' ? '' : 'style="display: none;"';?>>
            <th scope="row" class="titledesc">
                <label for="rfmp_redirect_error"><?php esc_html_e('Redirect URL failed payment', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_redirect_error" id="rfmp_redirect_error" value="<?php echo esc_attr($redirect_error);?>" type="text" style="width: 350px">
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_class_form"><?php esc_html_e('Class form', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_class_form" id="rfmp_class_form" value="<?php echo esc_attr($class_form);?>" type="text" style="width: 350px">
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="rfmp_antispam_method"><?php esc_html_e('Spam protection', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_antispam_method" id="rfmp_antispam_method">
                    <option value="none" <?php selected($antispamMethod, 'none');?>><?php esc_html_e('None', 'mollie-forms');?></option>
                    <option value="recaptcha" <?php selected($antispamMethod, 'recaptcha');?>><?php esc_html_e('Google reCAPTCHA v3', 'mollie-forms');?></option>
                    <option value="turnstile" <?php selected($antispamMethod, 'turnstile');?>><?php esc_html_e('Cloudflare Turnstile', 'mollie-forms');?></option>
                </select>
                <br><small><?php esc_html_e('Choose which spam protection to use on this form.', 'mollie-forms');?></small>
            </td>
        </tr>

        <tr valign="top" class="rfmp-antispam-row rfmp-antispam-recaptcha">
            <th scope="row" class="titledesc">
                <label for="rfmp_recaptcha_v3_site_key"><?php esc_html_e('Google reCAPTCHA v3 Site Key', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_recaptcha_v3_site_key" id="rfmp_recaptcha_v3_site_key" value="<?php echo esc_attr($recaptchaSiteKey);?>" type="text" style="width: 350px">
                <br><small>
                    <?php esc_html_e('Generate a reCAPTCHA v3 key at:', 'mollie-forms');?> <a target="_blank" href="https://www.google.com/recaptcha/admin/create">Google</a>
                </small>
            </td>
        </tr>

        <tr valign="top" class="rfmp-antispam-row rfmp-antispam-recaptcha">
            <th scope="row" class="titledesc">
                <label for="rfmp_recaptcha_v3_secret_key"><?php esc_html_e('Google reCAPTCHA v3 Secret Key', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_recaptcha_v3_secret_key" id="rfmp_recaptcha_v3_secret_key" value="<?php echo esc_attr($recaptchaSecretKey);?>" type="text" style="width: 350px">
            </td>
        </tr>

        <tr valign="top" class="rfmp-antispam-row rfmp-antispam-recaptcha">
            <th scope="row" class="titledesc">
                <label for="rfmp_recaptcha_v3_minimum_score"><?php esc_html_e('reCAPTCHA minimum acceptance score', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <select name="rfmp_recaptcha_v3_minimum_score" id="rfmp_recaptcha_v3_minimum_score">
                    <?php foreach([0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1.0] as $s): ?>
                        <option <?php echo ($recaptchaScore == $s ? 'selected' : '') ?>><?php echo esc_html($s) ?></option>
                    <?php endforeach ?>
                </select>
                <br><small><?php esc_html_e('Blocks submissions with score lower than this setting', 'mollie-forms');?></small>
            </td>
        </tr>

        <tr valign="top" class="rfmp-antispam-row rfmp-antispam-turnstile">
            <th scope="row" class="titledesc">
                <label for="rfmp_turnstile_site_key"><?php esc_html_e('Cloudflare Turnstile Site Key', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_turnstile_site_key" id="rfmp_turnstile_site_key" value="<?php echo esc_attr($turnstileSiteKey);?>" type="text" style="width: 350px">
                <br><small>
                    <?php esc_html_e('Generate a Turnstile key at:', 'mollie-forms');?> <a target="_blank" href="https://dash.cloudflare.com/?to=/:account/turnstile">Cloudflare</a>
                </small>
            </td>
        </tr>

        <tr valign="top" class="rfmp-antispam-row rfmp-antispam-turnstile">
            <th scope="row" class="titledesc">
                <label for="rfmp_turnstile_secret_key"><?php esc_html_e('Cloudflare Turnstile Secret Key', 'mollie-forms');?></label>
            </th>
            <td class="forminp forminp-text">
                <input name="rfmp_turnstile_secret_key" id="rfmp_turnstile_secret_key" value="<?php echo esc_attr($turnstileSecretKey);?>" type="text" style="width: 350px">
            </td>
        </tr>

        </tbody>
    </table>

    <script>
        (function () {
            var select = document.getElementById('rfmp_antispam_method');
            if (!select) {
                return;
            }

            function toggleAntispamRows() {
                var method = select.value;
                var rows   = document.querySelectorAll('.rfmp-antispam-row');
                for (var i = 0; i < rows.length; i++) {
                    var show = rows[i].classList.contains('rfmp-antispam-' + method);
                    rows[i].style.display = show ? '' : 'none';
                }
            }

            select.addEventListener('change', toggleAntispamRows);
            toggleAntispamRows();
        })();
    </script>
</div>

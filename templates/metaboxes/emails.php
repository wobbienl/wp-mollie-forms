<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class='inside'>
    <div id="rfmp_tabs">
        <ul>
            <li><a href="#rfmp_tab_paid_customer"><?php esc_html_e('Customer: Payment successful', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_expired_customer"><?php esc_html_e('Customer: Payment expired', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_cancelled_customer"><?php esc_html_e('Customer: Payment cancelled', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_chargedback_customer"><?php esc_html_e('Customer: Payment charged back', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_paid_merchant"><?php esc_html_e('Merchant: Payment successful', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_expired_merchant"><?php esc_html_e('Merchant: Payment expired', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_cancelled_merchant"><?php esc_html_e('Merchant: Payment cancelled', 'mollie-forms');?></a></li>
            <li><a href="#rfmp_tab_chargedback_merchant"><?php esc_html_e('Merchant: Payment charged back', 'mollie-forms');?></a></li>
        </ul>

        <div id="rfmp_tab_paid_customer">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_paid_customer" value="1" type="checkbox" <?php echo $enabled_paid_customer == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_paid_customer" value="<?php echo esc_attr($subject_paid_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_paid_customer" value="<?php echo esc_attr($fromemail_paid_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_paid_customer" value="<?php echo esc_attr($fromname_paid_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_paid_customer, 'rfmp_email_paid_customer', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_expired_customer">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_expired_customer" value="1" type="checkbox" <?php echo $enabled_expired_customer == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_expired_customer" value="<?php echo esc_attr($subject_expired_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_expired_customer" value="<?php echo esc_attr($fromemail_expired_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_expired_customer" value="<?php echo esc_attr($fromname_expired_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_expired_customer, 'rfmp_email_expired_customer', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_cancelled_customer">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_cancelled_customer" value="1" type="checkbox" <?php echo $enabled_cancelled_customer == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_cancelled_customer" value="<?php echo esc_attr($subject_cancelled_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_cancelled_customer" value="<?php echo esc_attr($fromemail_cancelled_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_cancelled_customer" value="<?php echo esc_attr($fromname_cancelled_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_cancelled_customer, 'rfmp_email_cancelled_customer', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_chargedback_customer">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_chargedback_customer" value="1" type="checkbox" <?php echo $enabled_chargedback_customer == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_chargedback_customer" value="<?php echo esc_attr($subject_chargedback_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_chargedback_customer" value="<?php echo esc_attr($fromemail_chargedback_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_chargedback_customer" value="<?php echo esc_attr($fromname_chargedback_customer);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_chargedback_customer, 'rfmp_email_chargedback_customer', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_paid_merchant">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_paid_merchant" value="1" type="checkbox" <?php echo $enabled_paid_merchant == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_paid_merchant" value="<?php echo esc_attr($subject_paid_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('To "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_toemail_paid_merchant" value="<?php echo esc_attr($toemail_paid_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_paid_merchant" value="<?php echo esc_attr($fromemail_paid_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_paid_merchant" value="<?php echo esc_attr($fromname_paid_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_paid_merchant, 'rfmp_email_paid_merchant', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_expired_merchant">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_expired_merchant" value="1" type="checkbox" <?php echo $enabled_expired_merchant == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_expired_merchant" value="<?php echo esc_attr($subject_expired_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('To "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_toemail_expired_merchant" value="<?php echo esc_attr($toemail_expired_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_expired_merchant" value="<?php echo esc_attr($fromemail_expired_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_expired_merchant" value="<?php echo esc_attr($fromname_expired_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_expired_merchant, 'rfmp_email_expired_merchant', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_cancelled_merchant">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_cancelled_merchant" value="1" type="checkbox" <?php echo $enabled_cancelled_merchant == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_cancelled_merchant" value="<?php echo esc_attr($subject_cancelled_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('To "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_toemail_cancelled_merchant" value="<?php echo esc_attr($toemail_cancelled_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_cancelled_merchant" value="<?php echo esc_attr($fromemail_cancelled_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_cancelled_merchant" value="<?php echo esc_attr($fromname_cancelled_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_cancelled_merchant, 'rfmp_email_cancelled_merchant', $rfmp_editor_settings); ?>
        </div>
        <div id="rfmp_tab_chargedback_merchant">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Enabled', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_enabled_chargedback_merchant" value="1" type="checkbox" <?php echo $enabled_chargedback_merchant == '1' ? 'checked' : '';?>>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('Subject', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_subject_chargedback_merchant" value="<?php echo esc_attr($subject_chargedback_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('To "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_toemail_chargedback_merchant" value="<?php echo esc_attr($toemail_chargedback_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "email"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromemail_chargedback_merchant" value="<?php echo esc_attr($fromemail_chargedback_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label><?php esc_html_e('From "name"', 'mollie-forms');?></label>
                    </th>
                    <td class="forminp forminp-text">
                        <input name="rfmp_fromname_chargedback_merchant" value="<?php echo esc_attr($fromname_chargedback_merchant);?>" type="text" style="width: 350px">
                    </td>
                </tr>
            </table>

            <?php wp_editor($email_chargedback_merchant, 'rfmp_email_chargedback_merchant', $rfmp_editor_settings); ?>
        </div>
    </div>

    <br>
    <?php esc_html_e('You can use variables in the subjects and messages.', 'mollie-forms');?><br>
    <?php esc_html_e('Visit our Help Center to learn more about variables:', 'mollie-forms');?> <a target="_blank" href="https://support.wobbie.nl/nl/help/articles/8197590-welke-variabelen-kan-ik-gebruiken">support.wobbie.nl</a>
</div>

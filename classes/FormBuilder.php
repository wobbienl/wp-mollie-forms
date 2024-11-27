<?php

namespace MollieForms;


class FormBuilder
{

    private $db, $mollieForms, $form, $postId, $label, $helpers, $recaptchaSiteKey;

    /**
     * MollieFormsBuilder constructor.
     *
     * @param $postId
     * @param $class
     */
    public function __construct($postId, $class = '')
    {
        global $wpdb;

        $this->db          = $wpdb;
        $this->mollieForms = new MollieForms();
        $this->helpers     = new Helpers();

        $this->postId           = $postId;
        $this->recaptchaSiteKey = sanitize_text_field(get_post_meta($postId, '_rfmp_recaptcha_v3_site_key', true));
        $this->form             = '';

        if ($this->recaptchaSiteKey) {
            $this->form .= '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($this->recaptchaSiteKey) . '"></script>';
            $this->form .= '<script>
                                function onSubmit' . $postId . '(e) {
                                    e.preventDefault(); 
							        grecaptcha.ready(function() {
							          grecaptcha.execute("' . esc_attr($this->recaptchaSiteKey) . '", {action: "submit"}).then(function(token) {
							              document.getElementById("rfmp_' . esc_js($postId) . '_token").value = token;
							              document.getElementById("rfmp_' . esc_js($postId) . '_submit").click();
							          });
							        });
                                }
                            </script>';
        }

        $this->form .= '<form enctype="multipart/form-data" data-mollie-forms="' . esc_attr($this->mollieForms->getVersion()) . '" method="post" id="rfmp_' . esc_attr($postId) . '" class="' . esc_attr($class) . '">';
        $this->form .= wp_nonce_field('mollie-forms', 'mollie_forms_' . $this->postId . '_nonce', true, false);
        $this->form .= '<input type="hidden" name="mollie-forms-post" value="' . $this->postId . '">';
    }

    /**
     * @param      $name
     * @param      $value
     * @param bool $required
     */
    public function addLabel($name, $value, $required = false)
    {
        $required = $required && $value ? ' <span class="mollie-forms-required">*</span>' : '';

        $this->label = '<label for="' . esc_attr($name) . '">' . strip_tags($value, '<a>') . $required . '</label>';
    }

    /**
     * Get and unset label
     *
     * @return mixed
     */
    private function getLabel()
    {
        $label       = $this->label;
        $this->label = '';

        return $label;
    }

    /**
     * @param       $type
     * @param array $atts
     *
     * @return mixed
     */
    public function addField($type, array $atts = [])
    {
        $visible = true;
	    $pId = '';
        switch ($type) {
            case 'text':
            case 'name':
            case 'address':
            case 'postalCode':
            case 'city':
                $html = '<input type="text" ' . $this->buildAtts($atts) . ' style="width: 100%">';
                break;
            case 'discount_code':
                $visible = isset($atts['required']);
                unset($atts['required']);
                $html = $visible ? '<input type="text" ' . $this->buildAtts($atts) . ' style="width: 100%">' : '';
                break;
            case 'country':
                $html = '<select ' . $this->buildAtts($atts) . ' style="width:100%;" style="width: 100%">';
                foreach ($this->helpers->getCountries() as $code => $country) {
                    $html .= '<option value="' . $code . '" ' .
                             (isset($atts['value']) && $atts['value'] == $code ? 'selected' : '') . '>' . $country .
                             '</option>';
                }
                $html .= '</select>';
                break;
            case 'email':
                $html = '<input type="email" ' . $this->buildAtts($atts) . ' style="width: 100%">';
                break;
            case 'date':
                $html = '<input type="date" ' . $this->buildAtts($atts) . ' style="width: 100%">';
                break;
            case 'checkbox':
                $html = '<input type="hidden" name="' . esc_attr($atts['name']) .
                        '" value="0"><input type="checkbox" value="1" ' . $this->buildAtts($atts, ['value']) . '> ' . $this->getLabel();
                break;
            case 'textarea':
                $html = '<textarea ' . $this->buildAtts($atts, ['value']) . ' style="width: 100%">' .
                        (isset($atts['value']) ? esc_attr($atts['value']) : '') . '</textarea>';
                break;
            case 'select':
            case 'dropdown':
                $html = '<select ' . $this->buildAtts($atts) . ' style="width:100%;" style="width: 100%">';

                if (isset($atts['options']) && is_array($atts['options'])) {
                    foreach ($atts['options'] as $option) {
                        $html .= '<option ' . (isset($atts['value']) && $atts['value'] == $option ? ' selected' : '') . '>' . esc_html($option) . '</option>';
                    }
                }

                $html .= '</select>';
                break;
            case 'radio':
                $html = '';

                if (isset($atts['options']) && is_array($atts['options'])) {
                    foreach ($atts['options'] as $option) {
                        $html .= '<label><input type="radio" ' . $this->buildAtts($atts, ['value']) . ' value="' .
                                 esc_attr($option) . '" ' .
                                 (isset($atts['value']) && $atts['value'] == $option ? ' selected' : '') . '>' . esc_html($option) . '</label>';
                    }
                }

                break;
            case 'submit':
                $html = '<br><button type="submit" ' . $this->buildAtts($atts) . '>' . $atts['label'] . '</button>';

                if ($this->recaptchaSiteKey) {
					$html = '<input type="hidden" id="rfmp_' . $this->postId . '_token" name="token" value="">';
					$html .= '<button type="submit" style="display:none;" id="rfmp_' . $this->postId . '_submit">' . $atts['label'] . '</button>';
                    $html .= '<br><button type="button" onclick="onSubmit' . $this->postId . '(event)" data-action="submit" ' .
                             $this->buildAtts($atts) . '>' . $atts['label'] . '</button>';
                }
                break;
            case 'file':
	            $html = '<input type="file" ' . $this->buildAtts($atts) . '>';
	            break;
            case 'payment_methods':
				$pId = 'payment_methods_' . $this->postId;
                $html = $this->getPaymentMethods($atts);
                break;
            case 'priceoptions':
                $html = $this->getPriceOptions($atts);
                break;
            case 'total':
                $visible = isset($atts['required']);
                $html = $visible ? $this->getTotal($atts) : '';
                break;
            case 'text-only':
            default:
                $html = '';
        }

        $this->form .= '<div class="mollie_forms_field_row" style="margin:10px 0" ' . ($pId ? 'id="' . $pId . '"' : '') . '>';

        if ($type != 'submit' && $type != 'checkbox' && $visible) {
            $this->form .= $this->getLabel() . '<br>';
        }

        $this->form .= $html;
        $this->form .= '</div>';
    }

    /**
     * @param $atts
     *
     * @return string
     */
    private function getPaymentMethods($atts)
    {
        $post = $this->postId;

        $apiKey    = get_post_meta($post, '_rfmp_api_key', true);
        $apiType   = get_post_meta($post, '_rfmp_api_type', true) ?: 'payments';
        $active    = get_post_meta($post, '_rfmp_payment_method', true);
        $fixed     = get_post_meta($post, '_rfmp_payment_method_fixed', true);
        $variable  = get_post_meta($post, '_rfmp_payment_method_variable', true);
        $display   = get_post_meta($post, '_rfmp_payment_methods_display', true);
	    $currency  = get_post_meta($post, '_rfmp_currency', true) ?: 'EUR';
        $formValue = isset($_POST['rfmp_payment_method']) ? sanitize_text_field($_POST['rfmp_payment_method']) : '';

        $locale = get_post_meta($post, '_rfmp_locale', true) ?: null;
        if (!$locale && key_exists(get_locale(), $this->helpers->getLocales())) {
            $locale = get_locale();
        }

        try {
            $mollie = new MollieApi($apiKey);

            $script = '';
            $rcur   = [];
            foreach ($mollie->all('methods', ['sequenceType' => 'first', 'amount' => ['value' => '1.00', 'currency' => $currency]]) as $method) {
                if (isset($active[$method->id]) && $active[$method->id]) {
                    $rcur[] = $method->id;
                    $script .= '    document.getElementById("rfmp_pm_' . $method->id . '_' . $post .
                               '").style.display = "block";' . "\n";
                }
            }
            foreach ($mollie->all('methods', ['locale' => $locale, 'resource' => $apiType, 'amount' => ['value' => '1.00', 'currency' => $currency], 'includeWallets' => 'applepay']) as $method) {
                if (isset($active[$method->id]) && $active[$method->id] && !in_array($method->id, $rcur)) {
                    $script .= 'if (document.getElementById("rfmp_pm_' . $method->id . '_' . $post . '") !== null){' .
                               "\n";
                    $script .= '    document.getElementById("rfmp_pm_' . $method->id . '_' . $post .
                               '").style.display = (frequency!="once" ? "none" : "block");' . "\n";
                    $script .= '}' . "\n";
                }
            }

            $methods = '
            <script>
            window.onload = setTimeout(mollie_forms_recurring_methods_' . $post . ', 100);
            function mollie_forms_recurring_methods_' . $post . '() {
                var priceoptions = document.getElementsByName("rfmp_priceoptions_' . $post . '");
                var freq = "";
                var frequency = "once";
                if (0 in priceoptions) {
                    if (priceoptions[0].tagName == "INPUT") {
                        for (var i = 0, length = priceoptions.length; i < length; i++) {
                            if (priceoptions[i].checked) {
                                frequency = priceoptions[i].dataset.frequency;
                                var pricetype = priceoptions[i].dataset.pricetype;
                                freq = priceoptions[i].dataset.freq;
                                break;
                            }
                        }
                    } else {
                        frequency = priceoptions[0].options[priceoptions[0].selectedIndex].dataset.frequency ? priceoptions[0].options[priceoptions[0].selectedIndex].dataset.frequency : "once";
                        var pricetype = priceoptions[0].options[priceoptions[0].selectedIndex].dataset.pricetype;
                        freq = priceoptions[0].options[priceoptions[0].selectedIndex].dataset.freq;
                    }
                } else {
                    var quantities = document.getElementsByClassName("rfmp_priceoptions_' . $post . '_quantity");
                    var pricetype = "fixed";
                    frequency = "once";
                    for (var i = 0, length = quantities.length; i < length; i++) {
                        if (quantities[i].value > 0) {
                            if (quantities[i].dataset.frequency != "once") {
                                frequency = "recurring";
                            }
                        }
                    }
                }

                var checkbox = document.getElementsByName("rfmp_checkbox_' . $post . '")[0];
                if (frequency=="once") {
                    checkbox.removeAttribute("required");
                } else {
                    checkbox.setAttribute("required", "required");
                }

                document.getElementById("rfmp_checkbox_' . $post . '").style.display = (frequency=="once" ? "none" : "block");
                document.getElementById("rfmp_checkbox_hidden_' . $post . '").value = (frequency=="once" ? 0 : 1);
                document.getElementById("rfmp_open_amount_' . $post . '").style.display = (pricetype=="open" ? "block" : "none");
                document.getElementById("rfmp_open_amount_required_' . $post . '").value = (pricetype=="open" ? 1 : 0);
                document.getElementById("rfmp_amount_freq_' . $post . '").innerHTML = freq;
                
                if (location.protocol === "https:" && window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
                    //
                } else if (document.getElementById("rfmp_pm_applepay_' . $post . '") !== null) {
                    document.getElementById("rfmp_pm_applepay_' . $post . '").remove();
                }
                
                ' . $script . '
            }
            </script>';

            $symbol = $this->helpers->getCurrencySymbol($currency);

            if ($display != 'dropdown') {
                $methods .= '<ul ' . $this->buildAtts($atts, ['name', 'value', 'placeholder']) .
                            ' style="list-style-type:none;margin:0;">';
            } else {
                $methods .= '<select ' . $this->buildAtts($atts, ['name', 'value', 'placeholder']) .
                            ' name="rfmp_payment_method_' . $post . '" style="width: 100%;" onchange="mollie_forms_' .
                            $post . '_totals();">';
            }

            $first = true;

            // loop through all payment methods
            foreach ($mollie->all('methods', ['locale' => $locale, 'resource' => $apiType, 'includeWallets' => 'applepay', 'amount' => ['value' => '1.00', 'currency' => $currency]]) as $method) {
                // check if method is enabled in form
                if (isset($active[$method->id]) && $active[$method->id]) {
                    $subcharge   = [];
                    $fixedFee    = 0;
                    $variableFee = 0;

                    // label for fixed fee
                    if (isset($fixed[$method->id]) && $fixed[$method->id]) {
                        $fixedFee    = str_replace(',', '.', $fixed[$method->id]);
                        $subcharge[] = $symbol . ' ' . $fixedFee;
                    }
                    $dataFixed = 'data-fixed="' . (isset($fixedFee) ? esc_attr($fixedFee) : 0) . '"';

                    // label for variable fee
                    if (isset($variable[$method->id]) && $variable[$method->id]) {
                        $variableFee = str_replace(',', '.', $variable[$method->id]);
                        $subcharge[] = $variableFee . '%';
                    }
                    $dataVariable = 'data-variable="' . (isset($variableFee) ? esc_attr($variableFee) : 0) . '"';

                    if ($display == 'list') {
                        // list with text and icons
                        $methods .= '<li id="rfmp_pm_' . esc_attr($method->id) . '_' . $post . '">
                                        <label>
                                            <input  type="radio" 
                                                    name="rfmp_payment_method_' . $post . '" 
                                                    value="' . esc_attr($method->id) . '"
                                                    ' . (isset($dataFixed) ? $dataFixed : '') . ' 
                                                    ' . (isset($dataVariable) ? $dataVariable : '') . ' 
                                                    onchange="mollie_forms_' . $post . '_totals();" 
                                                    ' . ($formValue == $method->id || $first ? ' checked' : '') . '> 
                                            <img    style="vertical-align:middle;display:inline-block;" 
                                                    src="' . esc_url($method->image->svg) . '"> 
                                            ' . esc_html($method->description) .
                                    (!empty($subcharge) ? ' (+ ' . esc_html(implode(' & ', $subcharge)) . ')' : '') . '
                                        </label>
                                     </li>';
                    } elseif ($display == 'text') {
                        // list with only text
                        $methods .= '<li id="rfmp_pm_' . esc_attr($method->id) . '_' . $post . '">
                                        <input  type="radio" 
                                                name="rfmp_payment_method_' . $post . '" 
                                                ' . (isset($dataFixed) ? $dataFixed : '') . ' 
                                                ' . (isset($dataVariable) ? $dataVariable : '') . ' 
                                                onchange="mollie_forms_' . $post . '_totals();" 
                                                value="' . esc_attr($method->id) . '"
                                                ' . ($formValue == $method->id || $first ? ' checked' : '') . '> 
                                        ' . esc_html($method->description) .
                                    (!empty($subcharge) ? ' (+ ' . esc_html(implode(' & ', $subcharge)) . ')' : '') . '
                                     </li>';
                    } elseif ($display == 'icons') {
                        // list with only icons
                        $methods .= '<li id="rfmp_pm_' . esc_attr($method->id) . '_' . $post . '">
                                        <input  type="radio" 
                                                name="rfmp_payment_method_' . $post . '" 
                                                value="' . esc_attr($method->id) . '"
                                                ' . (isset($dataFixed) ? $dataFixed : '') . ' 
                                                ' . (isset($dataVariable) ? $dataVariable : '') . ' 
                                                onchange="mollie_forms_' . $post . '_totals();" 
                                                ' . ($formValue == $method->id || $first ? ' checked' : '') . '> 
                                        <img    style="vertical-align:middle;display:inline-block;" 
                                                src="' . esc_url($method->image->svg) . '"> 
                                        ' . (!empty($subcharge) ? ' (+ ' . esc_html(implode(' & ', $subcharge)) . ')' : '') . '
                                     </li>';
                    } else {
                        // dropdown
                        $methods .= '<option    id="rfmp_pm_' . esc_attr($method->id) . '_' . $post . '" 
                                                value="' . esc_attr($method->id) . '"
                                                ' . (isset($dataFixed) ? $dataFixed : '') . ' 
                                                ' . (isset($dataVariable) ? $dataVariable : '') . ' 
                                                ' . ($formValue == $method->id ? ' selected' : '') . '>
                                        ' . esc_html($method->description) .
                                    (!empty($subcharge) ? ' (+ ' . esc_html(implode(' & ', $subcharge)) . ')' : '') . '
                                     </option>';
                    }

                    $first = false;
                }
            }

            if ($display != 'dropdown') {
                $methods .= '</ul>';
            } else {
                $methods .= '</select>';
            }

            $methods .= '<input type="hidden" id="rfmp_checkbox_hidden_' . $post . '" name="rfmp_checkbox_hidden_' . $post . '" value="0">';
            $methods .= '<br>';
            $methods .= '<label id="rfmp_checkbox_' . $post . '" style="display:none;">
                            <input type="checkbox" name="rfmp_checkbox_' . $post . '" value="1">' .
                        esc_html__('I hereby give authorization to collect the recurring amount from my account periodically.', 'mollie-forms') . '
                         </label>';

        } catch (Exception $e) {
            $methods = '<p style="color: red">' . esc_html($e->getMessage()) . '</p>';
        }

        return $methods;
    }

    /**
     * Get all price options
     *
     * @param $atts
     *
     * @return string
     */
    private function getPriceOptions($atts)
    {
        $post = $this->postId;

        $priceOptions  = $this->db->get_results($this->db->prepare("SELECT * FROM {$this->mollieForms->getPriceOptionsTable()} WHERE post_id=%d ORDER BY sort_order ASC", $post));
        $optionDisplay = get_post_meta($post, '_rfmp_priceoptions_display', true);
        $vatSetting    = get_post_meta($post, '_rfmp_vat_setting', true);
        $shippingCosts = get_post_meta($post, '_rfmp_shipping_costs', true);

        $currency = get_post_meta($post, '_rfmp_currency', true) ?: 'EUR';
        $decimals = $this->helpers->getCurrencies($currency);
        $symbol   = $this->helpers->getCurrencySymbol($currency);

        $formValue       = sanitize_text_field(isset($_POST['rfmp_priceoptions_' . $post]) ? $_POST['rfmp_priceoptions_' . $post] :
	        (isset($_GET['form_' . $post . '_priceoption']) ? $_GET['form_' . $post . '_priceoption'] : ''));
        $formValueAmount = sanitize_text_field(isset($_POST['rfmp_amount_' . $post]) ? $_POST['rfmp_amount_' . $post] :
	        (isset($_GET['form_' . $post . '_amount']) ? $_GET['form_' . $post . '_amount'] : ''));


        $html  = '';
		$first = true;
        if ($optionDisplay == 'list') {
            $html .= '<ul ' . $this->buildAtts($atts, ['name', 'placeholder', 'value']) .
                     ' style="list-style-type:none;margin:0;">';
        } elseif ($optionDisplay == 'table_quantity') {
            $html .= '<table ' . $this->buildAtts($atts, ['name', 'placeholder', 'value']) . '>';
        } else {
            $html .= '<select name="rfmp_priceoptions_' . $post . '" onchange="mollie_forms_recurring_methods_' .
                     $post . '();mollie_forms_' . $post . '_totals();" ' .
                     $this->buildAtts($atts, ['name', 'placeholder', 'value', 'label']) . ' style="width: 100%;">';

			if (count($priceOptions) > 1) {
				$html .= '<option value="">-- ' . esc_html__('Choose an option', 'mollie-forms') . ' --</option>';
			}
        }

        // loop through all price options
        foreach ($priceOptions as $priceOption) {
            if ($priceOption->stock != '' && $priceOption->stock <= 0) {
                continue;
            }

            // set correct price label with frequency
            $frequency = $priceOption->frequency != 'once' ?
                $priceOption->frequency_value . ' ' . $priceOption->frequency : 'once';
            if ($priceOption->price_type != 'open') {
                $price = $symbol . ' ' . number_format($priceOption->price, $decimals, ',', '') . ' ' .
                         $this->helpers->getFrequencyLabel($frequency);
            } else {
                $price = $this->helpers->getFrequencyLabel($frequency);
            }

            // if option has number of times, show label
            $times = $priceOption->times > 0 ?
	            /* translators: %s is the number of times */
                '; ' . sprintf(esc_html__('Stops after %s times', 'mollie-forms'), $priceOption->times) : '';

            // check if is list or select to add correct html
            if ($optionDisplay == 'list') {
                // list view, only 1 option
                $html  .= '<li>
                            <label>
                                <input type="radio" 
                                        onchange="mollie_forms_recurring_methods_' . $post . '();mollie_forms_' .
                          $post . '_totals();" 
                                        data-frequency="' . esc_attr($priceOption->frequency) . '" 
                                        data-freq="' . esc_attr($this->helpers->getFrequencyLabel($frequency)) . '" 
                                        data-pricetype="' . esc_attr($priceOption->price_type) . '" 
                                        data-price="' . esc_attr($priceOption->price) . '" 
                                        data-vat="' . esc_attr($priceOption->vat) . '" 
                                        name="rfmp_priceoptions_' . $post . '" 
                                        value="' . esc_attr($priceOption->id) . '"
                                        ' . ($formValue == $priceOption->id || ($first && count($priceOptions) === 1) ? ' checked' : '') . '> 
                                ' . esc_html($priceOption->description) . ' ' .
                          ($price || $times ? '(' . esc_html(trim($price . $times)) . ')' : '') . '
                            </label>
                          </li>';
            } elseif ($optionDisplay == 'table_quantity') {
                // table view to select multiple options with quantity
                if ($priceOption->price_type != 'open') {
                    $html .= '<tr>
                            <td style="min-width: 150px;">
                                <input  type="number"
                                        name="rfmp_priceoptions_' . $post . '_quantity[' . $priceOption->id . ']"
                                        class="rfmp_priceoptions_' . $post . '_quantity"
                                        data-frequency="' . esc_attr($priceOption->frequency) . '" 
                                        data-freq="' . esc_attr($this->helpers->getFrequencyLabel($frequency)) . '" 
                                        data-price="' . esc_attr($priceOption->price) . '" 
                                        data-vat="' . esc_attr($priceOption->vat) . '" 
                                        onchange="mollie_forms_recurring_methods_' . $post . '();mollie_forms_' .
                             $post . '_totals();"
                                        min="0"
                                        ' . ($priceOption->stock > 0 ? 'max="' . esc_attr($priceOption->stock) . '"' : '') . '
                                        value="0">
                            </td>
                            <td>
                                <strong>' . esc_html($priceOption->description) . '</strong><br>
                                <small>' . esc_html(trim($price . $times)) . '</small>
                            </td>
                          </tr>';
                }
            } else {
                // dropdown view
                $html .= '<option   data-frequency="' . esc_attr($priceOption->frequency) . '" 
                                    data-freq="' . esc_attr($this->helpers->getFrequencyLabel($frequency)) . '" 
                                    data-pricetype="' . esc_attr($priceOption->price_type) . '" 
                                    data-price="' . esc_attr($priceOption->price) . '" 
                                    data-vat="' . esc_attr($priceOption->vat) . '" 
                                    value="' . esc_attr($priceOption->id) . '" ' . ($formValue == $priceOption->id ? ' selected' : '') . '>
                                ' . esc_html($priceOption->description) .
                         ($price || $times ? ' (' . esc_html(trim($price . $times)) . ')' : '') . '
                          </option>';
            }

	        $first = false;
        }

        if ($optionDisplay == 'list') {
            $html .= '</ul>';
        } elseif ($optionDisplay == 'table_quantity') {
            $html .= '</table>';
        } else {
            $html .= '</select>';
        }


        $html .= '<p id="rfmp_open_amount_' . $post . '" style="display:none;">
                    <label>' . esc_html__('Amount', 'mollie-forms') . ' 
                        <span style="color:red;">*</span><br>
                        <span class="rfmp_currency_' . $post . '">' . esc_html($symbol) . '</span> 
                        <input type="number" step="any" value="' . esc_attr($formValueAmount) . '" onchange="mollie_forms_' . $post . '_totals();" onkeyup="mollie_forms_' . $post . '_totals();" name="rfmp_amount_' . $post . '"> 
                        <span id="rfmp_amount_freq_' . $post . '"></span>
                    </label>
                    <input type="hidden" name="rfmp_amount_required_' . $post . '" id="rfmp_open_amount_required_' . $post . '" value="0">
                  </p>';

        $html .= '
        <script>
        window.onload = setTimeout(mollie_forms_' . $post . '_totals, 100);
        function mollie_forms_' . $post . '_totals() {
            var priceoption = document.getElementsByName("rfmp_priceoptions_' . $post . '");
            var quantities  = document.getElementsByClassName("rfmp_priceoptions_' . $post . '_quantity");
            var subtotal = 0, total = 0, vat = 0;
            
            
            // Add shipping costs to total
            var shippingCosts = "' .
                 ($shippingCosts ? number_format(str_replace(',', '.', $shippingCosts), $decimals, '.', '') : '') . '";
            if (shippingCosts) {
                var shippingVat = 0.21 * parseFloat(shippingCosts);
                vat   += shippingVat;
                total += parseFloat(shippingCosts);
                subtotal += parseFloat(shippingCosts);
                ' . ($vatSetting == 'excl' ? 'total += shippingVat;' : '') . '
            }
            
            if (0 in priceoption) {
                var openAmount  = document.getElementsByName("rfmp_amount_' . $post . '");
            
                // single price option
                if (priceoption[0].tagName == "INPUT") {
                    for (var i = 0, length = priceoption.length; i < length; i++) {
                        if (priceoption[i].checked) {
                        
                            if (priceoption[i].dataset.pricetype == "open") {
                                openAmount[0].setAttribute("min", isNaN(priceoption[i].dataset.price) ? 0 : priceoption[i].dataset.price);
                                var optionPrice = parseFloat(openAmount[0].value);
                            } else {
                                openAmount[0].removeAttribute("min");
                                var optionPrice = parseFloat(priceoption[i].dataset.price);
                            }

                            if (optionPrice <= 0 || isNaN(optionPrice)) {
                                break;
                            }
                            
                            var optionVat = (parseInt(priceoption[i].dataset.vat) / 100) * optionPrice;
                        
                            vat += optionVat;
                            total += optionPrice;
                            subtotal += optionPrice;
                            ' . ($vatSetting == 'excl' ? 'total += optionVat;' : '') . '
                            break;
                        }
                    }
                } else {
                    if (priceoption[0].options[priceoption[0].selectedIndex].dataset.pricetype == "open") {
                        openAmount[0].setAttribute("min", isNaN(priceoption[0].options[priceoption[0].selectedIndex].dataset.price) ? 0 : priceoption[0].options[priceoption[0].selectedIndex].dataset.price);
                        var optionPrice = parseFloat(openAmount[0].value);
                    } else {
                        openAmount[0].removeAttribute("min");
                        var optionPrice = parseFloat(priceoption[0].options[priceoption[0].selectedIndex].dataset.price);
                    }
                            
                    if (optionPrice > 0 || isNaN(optionPrice)) {
                        var optionVat = (parseInt(priceoption[0].options[priceoption[0].selectedIndex].dataset.vat) / 100) * optionPrice;
                        vat   += optionVat;
                        total += optionPrice;
                        subtotal += optionPrice;
                        ' . ($vatSetting == 'excl' ? 'total += optionVat;' : '') . '
                    }
                }
            } else if (quantities) {
                // multiple price options with quantity
                for (var i = 0; i < quantities.length; i++) {
                    var q = parseInt(quantities[i].value);
                    if (q <= 0 || isNaN(q)) {
                        continue;
                    }
                    
                    var optionPrice = parseFloat(quantities[i].dataset.price) * q;
                    if (optionPrice > 0 || isNaN(optionPrice)) {
                        var optionVat = (parseInt(quantities[i].dataset.vat) / 100) * optionPrice;
                        vat   += optionVat;
                        total += optionPrice;
                        subtotal += optionPrice;
                        ' . ($vatSetting == 'excl' ? 'total += optionVat;' : '') . '
                    }
                }
            }
            
            // payment method extra costs
            var methods = document.getElementsByName("rfmp_payment_method_' . $post . '");
            if (total > 0) {
	            if (0 in methods) {
	                if (methods[0].tagName == "INPUT") {
	                    // radio buttons
	                    for (var i = 0; i < methods.length; i++) {
	                        if (methods[i].checked) {
	                            var methodAmount = ((parseInt(methods[i].dataset.variable) / 100) * total) + parseFloat(methods[i].dataset.fixed);
	                            var methodVat = 0.21 * methodAmount;
	                            vat   += methodVat;
	                            total += methodAmount;
	                            subtotal += methodAmount;
	                            ' . ($vatSetting == 'excl' ? 'total += methodVat;' : '') . '
	                            break;
	                        }
	                    }
	                } else {
	                    // dropdown
	                    var methodAmount = ((parseInt(methods[0].options[methods[0].selectedIndex].dataset.variable) / 100) * total) + parseFloat(methods[0].options[methods[0].selectedIndex].dataset.fixed);
	                    var methodVat = 0.21 * methodAmount;
	                    vat   += methodVat;
	                    total += methodAmount;
	                    subtotal += methodAmount;
	                    ' . ($vatSetting == 'excl' ? 'total += methodVat;' : '') . '
	                }
	            }
            }

            // Display subtotal
            var subtotalValue = document.getElementById("rfmp_totals_' . $post . '_subtotal_value");
            if (subtotalValue) {
                var subtotalAmount = subtotal.toFixed(2) > 0 ? subtotal.toFixed(2) : "0.00";
                subtotalValue.innerHTML = subtotalAmount.replace(".", ",");
            }
            
            // Display total
            var totalValue = document.getElementById("rfmp_totals_' . $post . '_total_value");
            if (totalValue) {
                var totalAmount = total.toFixed(2) > 0 ? total.toFixed(2) : "0.00";
                totalValue.innerHTML = totalAmount.replace(".", ",");
            }
            
            // Display VAT
            var totalVatValue = document.getElementById("rfmp_totals_' . $post . '_vat_value");
            if (totalVatValue) {
                var totalVat = vat.toFixed(2) > 0 ? vat.toFixed(2) : "0.00";
                totalVatValue.innerHTML = totalVat.replace(".", ",");
            }
            
            
            if (total <= 0 || isNaN(total)) {
                document.getElementById("payment_methods_' . $post . '").style.display = "none";
            } else {
                document.getElementById("payment_methods_' . $post . '").style.display = "block";
            }
        }
        </script>';

        return $html;
    }

    /**
     * @param $atts
     *
     * @return string
     */
    private function getTotal($atts)
    {
        $post          = $this->postId;
        $currency      = get_post_meta($post, '_rfmp_currency', true) ?: 'EUR';
        $symbol        = $this->helpers->getCurrencySymbol($currency);
        $shippingCosts = get_post_meta($post, '_rfmp_shipping_costs', true);
        $vatSetting    = get_post_meta($post, '_rfmp_vat_setting', true);

        $html = '<table ' . $this->buildAtts($atts, ['name', 'placeholder', 'value']) . '>';

        // display shipping costs
        if ($shippingCosts) {
            $html .= '<tr>
                        <td>' . esc_html__('Shipping costs', 'mollie-forms') . '</td>
                        <td>' . esc_html($symbol . ' ' . number_format(str_replace(',', '.', $shippingCosts), 2, ',', '')) . '</td>
                      </tr>';
        }

        // check if prices are excl VAT
        if ($vatSetting == 'excl') {
            $html .= '<tr id="rfmp_totals_' . $post . '_subtotal">
                        <td>' . esc_html__('Subtotal', 'mollie-forms') . '</td>
                        <td>' . esc_html($symbol) . ' <span id="rfmp_totals_' . $post . '_subtotal_value"></span></td>
                      </tr>';

            $html .= '<tr id="rfmp_totals_' . $post . '_vat">
                        <td>' . esc_html__('VAT', 'mollie-forms') . '</td>
                        <td>' . esc_html__($symbol) . ' <span id="rfmp_totals_' . $post . '_vat_value"></span></td>
                      </tr>';
        }

        $html .= '<tr id="rfmp_totals_' . $post . '_total">
                    <td><strong>' . esc_html__('Total', 'mollie-forms') . '</strong></td>
                    <td><strong>' . esc_html($symbol) . ' <span id="rfmp_totals_' . $post . '_total_value"></span></strong></td>
                  </tr>';


        $html .= '</table>';

        return $html;
    }

    /**
     * Build form attributes
     *
     * @param array $atts
     * @param array $unset
     *
     * @return string
     */
    public function buildAtts(array $atts, array $unset = [])
    {
        if (empty($atts)) {
            return '';
        }

        $html = [];
        foreach ($atts as $key => $value) {
	        if ($key === 'options' || is_array($value)) {
		        continue;
	        }

            if (!in_array($key, $unset) && !is_array($value)) {
                $html[] = esc_html($key) . (isset($value) && $value ? '="' . esc_attr($value) . '"' : '');
            }
        }

        return implode(' ', $html);
    }

    /**
     * Render the form
     *
     * @return string
     */
    public function render()
    {
        $this->form .= '</form>';
        return $this->form;
    }

}

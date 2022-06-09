<?php

namespace MollieForms;


use DateTime;

class Form
{

    private $db, $mollieForms, $helpers;

    /**
     * MollieFormsForm constructor.
     *
     * @param MollieForms $plugin
     */
    public function __construct($plugin)
    {
        global $wpdb;

        $this->db          = $wpdb;
        $this->mollieForms = $plugin;
        $this->helpers     = new Helpers();

        $this->initForm();
    }

    /**
     * Initialize form
     */
    public function initForm()
    {
        add_shortcode('rfmp', [$this, 'addMollieFormsShortcode']);
        add_shortcode('mollie-forms', [$this, 'addMollieFormsShortcode']);
        add_shortcode('rfmp-total', [$this, 'addMollieFormsTotalShortcode']);
        add_shortcode('mollie-forms-total', [$this, 'addMollieFormsTotalShortcode']);
        add_shortcode('rfmp-goal', [$this, 'addMollieFormsGoalShortcode']);
        add_shortcode('mollie-forms-goal', [$this, 'addMollieFormsGoalShortcode']);
    }

    /**
     * Mollie Forms Shortcode
     *
     * @param $atts
     *
     * @return string
     * @throws Exception
     */
    public function addMollieFormsShortcode($atts)
    {
        $atts = shortcode_atts(['id' => ''], $atts);

        if (!isset($atts['id']) || empty($atts['id'])) {
            return __('Please pass the form ID to the shortcode', 'mollie-forms');
        }

        $post = get_post($atts['id']);
        if (!isset($post->ID) || !$post->ID) {
            return __('Form not found', 'mollie-forms');
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mollie-forms-post']) &&
            $_POST['mollie-forms-post'] == $post->ID) {
            return $this->processSubmit($post->ID);
        }

        if (isset($_GET['payment'])) {
            return $this->processRedirect($post->ID, $_GET['payment']);
        }

        $locale    = get_post_meta($post->ID, '_rfmp_locale', true) ?: 'nl_NL';
        $formClass = get_post_meta($post->ID, '_rfmp_class_form', true);
        $builder   = new FormBuilder($post->ID, $formClass);

        $fields = get_post_meta($post->ID, '_rfmp_fields_type', true);

        foreach ($fields as $key => $type) {
            $label        = get_post_meta($post->ID, '_rfmp_fields_label', true);
            $options      = get_post_meta($post->ID, '_rfmp_fields_value', true);
            $class        = get_post_meta($post->ID, '_rfmp_fields_class', true);
            $required     = get_post_meta($post->ID, '_rfmp_fields_required', true);
            $labelDisplay = get_post_meta($post->ID, '_rfmp_label_display', true);

            $name = 'form_' . $post->ID . '_field_' . $key;

            $atts = [
                'name'  => $name,
                'id'    => $name,
                'value' => isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : ''),
                'label' => $label[$key],
                'class' => $class[$key],
            ];

            if ($required[$key]) {
                $atts['required'] = '';
            }

            $defaultCountry = explode('_', $locale);
            if ($type == 'country' && $atts['value'] == '' && isset($defaultCountry[1])) {
                $atts['value'] = $defaultCountry[1];
            }

            if ($options[$key]) {
                $atts['options'] = explode('|', $options[$key]);
            }

            if ($labelDisplay == 'placeholder' || $labelDisplay == 'both') {
                $atts['placeholder'] = $label[$key] . ($required[$key] ? ' *' : '');
            }

            if ($labelDisplay != 'placeholder') {
                $required = !($type == 'discount_code') && isset($atts['required']);
                $builder->addLabel($atts['name'], $atts['label'], $required);
            }

            $builder->addField($type, $atts);
        }

        return $builder->render();
    }

    public function addMollieFormsTotalShortcode($atts)
    {
        $atts = shortcode_atts([
            'id'    => '',
            'start' => 0.00,
        ], $atts);

        $total = (float) $atts['start'];

        foreach (explode(',', str_replace(' ', '', $atts['id'])) as $formId) {
            $post = get_post($formId);

            if (!$post->ID) {
                return 'Form with ID ' . $formId . ' not found';
            }

            $total += $this->db->get_var("SELECT SUM(payments.amount) FROM {$this->mollieForms->getPaymentsTable()} payments INNER JOIN {$this->mollieForms->getRegistrationsTable()} registrations ON payments.registration_id = registrations.id AND registrations.post_id='" .
                                         esc_sql($post->ID) .
                                         "' WHERE payments.payment_status='paid' AND payments.payment_mode='live'");
        }

        $currency = get_post_meta($post->ID, '_rfmp_currency', true) ?: 'EUR';
        $decimals = $this->helpers->getCurrencies($currency);
        $symbol   = $this->helpers->getCurrencySymbol($currency);

        return $symbol . ' ' . number_format($total, $decimals, ',', '.');
    }

    /**
     * Goal shortcode
     *
     * @param $atts
     *
     * @return string
     */
    public function addMollieFormsGoalShortcode($atts)
    {
        $atts = shortcode_atts([
            'id'   => '',
            'goal' => '',
            'text' => __('Goal reached!', 'mollie-forms'),
        ], $atts);
        $post = get_post($atts['id']);
        $goal = $atts['goal'];

        if (!$post->ID) {
            return __('Form not found', 'mollie-forms');
        }

        if ($goal < 0) {
            return __('Goal must be higher then 0', 'mollie-forms');
        }

        $total = $this->db->get_var("SELECT SUM(payments.amount) FROM {$this->mollieForms->getPaymentsTable()} payments INNER JOIN {$this->mollieForms->getRegistrationsTable()} registrations ON payments.registration_id = registrations.id AND registrations.post_id='" .
                                    esc_sql($post->ID) .
                                    "' WHERE payments.payment_status='paid' AND payments.payment_mode='live'");

        $goal = (int) $goal - $total;

        if ($goal <= 0) {
            return __($atts['text'], 'mollie-forms');
        }

        $currency = get_post_meta($post->ID, '_rfmp_currency', true) ?: 'EUR';
        $decimals = $this->helpers->getCurrencies($currency);
        $symbol   = $this->helpers->getCurrencySymbol($currency);

        return $symbol . ' ' . number_format($goal, $decimals, ',', '.');
    }

    /**
     * Submit form
     *
     * @param $postId
     *
     * @return string
     * @throws Exception
     */
    private function processSubmit($postId)
    {
        $apiKey   = get_post_meta($postId, '_rfmp_api_key', true);
        $apiType  = get_post_meta($postId, '_rfmp_api_type', true) ?: 'payments';
        $webhook  = get_home_url(null, $this->mollieForms->getWebhookUrl() . $postId);
        $redirect = (is_ssl() ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $redirect .= strstr($redirect, '?') ? '&' : '?';

        do_action('rfmp_form_submitted', $postId, $_POST);

		$recaptchaSecretKey = get_post_meta($postId, '_rfmp_recaptcha_v3_secret_key', true);

		if ($recaptchaSecretKey) {
			$response = wp_remote_request(
				"https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptchaSecretKey . "&response=" . $_POST['token'],
				[
					'method'     => 'GET',
					'timeout'    => 45,
					'blocking'   => true,
				]
			);
			$response = json_decode($response['body']);

			if ($response->success === false || $response->score <= 0.5) {
				throw new Exception('Spam');
			}
		}

        try {

            if (!$apiKey) {
                echo '<p style="color: red">' . esc_html__('No API-key set', 'mollie-forms') . '</p>';
            } else {
                $mollie = new MollieApi($apiKey);
                $rfmpId = uniqid('rfmp-' . $postId . '-');

                $field_type  = get_post_meta($postId, '_rfmp_fields_type', true);
                $field_label = get_post_meta($postId, '_rfmp_fields_label', true);

                $name_field        = array_search('name', $field_type);
                $email_field       = array_search('email', $field_type);
                $name_field_value  = trim($_POST['form_' . $postId . '_field_' . $name_field]);
                $email_field_value = trim($_POST['form_' . $postId . '_field_' . $email_field]);

                $locale   = get_post_meta($postId, '_rfmp_locale', true) ?: null;
                $currency = get_post_meta($postId, '_rfmp_currency', true) ?: 'EUR';
                $decimals = $this->helpers->getCurrencies($currency);
                $symbol   = $this->helpers->getCurrencySymbol($currency);

                $vatSetting = get_post_meta($postId, '_rfmp_vat_setting', true);

                // Create customer at Mollie
                $customer = $mollie->post('customers', [
                    'name'  => $name_field_value,
                    'email' => $email_field_value,
                ]);

                // create customer in database
                $this->db->insert($this->mollieForms->getCustomersTable(), [
                    'created_at'  => current_time('mysql', 1),
                    'post_id'     => $postId,
                    'customer_id' => $customer->id,
                    'name'        => $customer->name,
                    'email'       => $customer->email,
                ]);
                $customerId = $this->db->insert_id;

                do_action('rfmp_customer_created', $postId, $customer);

                // get price options
                $priceOptions = [];
                $optionsDesc  = [];
                if (isset($_POST['rfmp_priceoptions_' . $postId . '_quantity'])) {
                    // multiple price options
                    foreach ($_POST['rfmp_priceoptions_' . $postId . '_quantity'] as $optionId => $quantity) {
                        if ($quantity <= 0) {
                            continue;
                        }

                        $option         = $this->db->get_row("SELECT * FROM {$this->mollieForms->getPriceOptionsTable()} WHERE id=" .
                                                             (int) $optionId);
                        $priceOptions[] = [
                            'option'   => $option,
                            'quantity' => $quantity,
                        ];
                        $optionsDesc[]  = $quantity . 'x ' . $option->description;
                    }
                } else {
                    // single price option
                    $option         = $this->db->get_row("SELECT * FROM {$this->mollieForms->getPriceOptionsTable()} WHERE id=" .
                                                         (int) $_POST['rfmp_priceoptions_' . $postId]);
                    $priceOptions[] = [
                        'option'   => $option,
                        'quantity' => 1,
                    ];
                    $optionsDesc[]  = '1x ' . $option->description;
                }

	            if (empty($priceOptions)) {
		            throw new Exception(__('Please select at least 1 product', 'mollie-forms'));
	            }

                // calc total amounts
                $totalPrice = 0.00;
                $totalVat   = 0.00;
                $recurring  = false;
                $vatRateFirstPriceOption = null;
                foreach ($priceOptions as $priceOption) {
                    $price = $priceOption['option']->price_type == 'open' ?
                        (isset($_POST['rfmp_amount_' . $postId]) ?
                            str_replace(',', '.', $_POST['rfmp_amount_' . $postId]) : 0) :
                        $priceOption['option']->price;

                    if ($priceOption['option']->frequency != 'once') {
                        $recurring = true;
                    }

                    $optionPrice = $price * $priceOption['quantity'];

                    if ($vatSetting === 'excl') {
                        $optionVat  = ($priceOption['option']->vat / 100) * $optionPrice;
                        $totalPrice += $optionVat;
                    } else {
                        $optionVat = ($priceOption['option']->vat / (100 + $priceOption['option']->vat)) * $optionPrice;
                    }

                    $totalVat   += $optionVat;
                    $totalPrice += $optionPrice;

                    if ($vatRateFirstPriceOption === null) {
                        $vatRateFirstPriceOption = $priceOption['option']->vat;
                    }
                }

                if ($vatRateFirstPriceOption === null) {
                    $vatRateFirstPriceOption = 21;
                }

                // calc shipping costs
                $shippingCosts = get_post_meta($postId, '_rfmp_shipping_costs', true);
                if ($shippingCosts) {
                    $shippingPrice = (float) number_format(str_replace(',', '.', $shippingCosts), $decimals, '.', '');

                    if ($vatSetting === 'excl') {
                        $shippingVat = 0.21 * $shippingPrice;
                        $totalPrice  += $shippingVat;
                    } else {
                        $shippingVat = $shippingPrice * (21 / 121);
                    }

                    $totalPrice += $shippingPrice;
                    $totalVat   += $shippingVat;
                }

                // extra payment method costs
                $paymentMethod = $_POST['rfmp_payment_method_' . $postId];
                $paymentCosts  = 0;
                $fixed         = get_post_meta($postId, '_rfmp_payment_method_fixed', true);
                $variable      = get_post_meta($postId, '_rfmp_payment_method_variable', true);

                if (isset($fixed[$paymentMethod]) && $fixed[$paymentMethod]) {
                    $paymentCosts += (float) number_format(str_replace(',', '.', $fixed[$paymentMethod]), $decimals, '.', '');
                }

                if (isset($variable[$paymentMethod]) && $variable[$paymentMethod]) {
                    $paymentCosts += ($variable[$paymentMethod] / 100) * $totalPrice;
                }

                if ($paymentCosts > 0) {
                    if ($vatSetting === 'excl') {
                        $paymentVat = 0.21 * $paymentCosts;
                        $totalPrice += $paymentVat;
                    } else {
                        $paymentVat = $paymentCosts * (21 / 121);
                    }

                    $totalPrice += $paymentCosts;
                    $totalVat   += $paymentVat;
                }

                // description
                $search_desc  = [
                    '{rfmp="id"}',
                    '{rfmp="priceoption"}',
                    '{rfmp="form_title"}',
                ];
                $replace_desc = [
                    $rfmpId,
                    implode(', ', $optionsDesc),
                    get_the_title($postId),
                ];

                // Add field values of registration
                foreach ($field_label as $key => $field) {
                    if ($field_type[$key] == 'submit' || $field_type[$key] == 'total') {
                        continue;
                    }

                    $value = isset($_POST['form_' . $postId . '_field_' . $key]) ?
                        $_POST['form_' . $postId . '_field_' . $key] : '';
                    if ($field_type[$key] === 'payment_methods') {
                        $value = $_POST['rfmp_payment_method_' . $postId];
                    } elseif ($field_type[$key] === 'priceoptions') {
                        $value = implode(', ', $optionsDesc);
                    }

					$required = get_post_meta($postId, '_rfmp_fields_required', true);
					if ($field_type[$key] != 'discount_code' && $required[$key] && empty($value)) {
						throw new Exception(sprintf(__( '%s is a required field', 'mollie-forms'), $field));
					}

                    $search_desc[]  = '{rfmp="' . trim($field) . '"}';
                    $replace_desc[] = $value;

                    if ($field_type[$key] == 'discount_code') {
                        $discountCode = $value;
                    }
                }

                // Discount
                if (isset($discountCode) && !empty($discountCode)) {
                    $discount = $this->db->get_row("SELECT * FROM {$this->mollieForms->getDiscountCodesTable()} WHERE post_id = " . (int) $postId . " AND discount_code = '" . trim(esc_sql($discountCode)) . "'");

                    if ($discount !== null) {
                        $now = new DateTime('now', wp_timezone());
                        $validFrom = new DateTime($discount->valid_from, wp_timezone());
                        $validUntil = new DateTime($discount->valid_until, wp_timezone());

                        if ($discount->times_max > 0 && $discount->times_used >= $discount->times_max) {
                            // Max number of usages
                            throw new Exception(__('The discount code has expired', 'mollie-forms'));
                        }

                        if ($now->getTimestamp() < $validFrom->getTimestamp() || $now->getTimestamp() > $validUntil->getTimestamp()) {
                            // Not valid anymore
                            throw new Exception(__('The discount code has expired', 'mollie-forms'));
                        }

                        $discountAmount = (float)$discount->discount;
                        if ($discount->discount_type === 'percentage') {
                            $discountPercentage = ((int) $discount->discount) / 100;
                            $discountAmount = $totalPrice * $discountPercentage;
                        }

                        if ($vatSetting === 'excl') {
                            $discountVat = ($vatRateFirstPriceOption / 100) * $discountAmount;
                        } else {
                            $discountVat = $discountAmount * ($vatRateFirstPriceOption / 121);
                        }

                        $totalPrice -= $discountAmount;
                        $totalVat   -= $discountVat;
                    }
                }

                $search_desc[] = '{rfmp="amount"}';
                $replace_desc[] = $symbol . ' ' . number_format($totalPrice, $decimals, ',', '');

                // description
                $desc = get_post_meta($postId, '_rfmp_payment_description', true);
                if (!$desc) {
                    $desc = '{rfmp="priceoption"}';
                }

                $desc = str_replace($search_desc, $replace_desc, $desc);

                // create registration
                $this->db->insert($this->mollieForms->getRegistrationsTable(), [
                    'post_id'     => $postId,
                    'created_at'  => current_time('mysql', 1),
                    'customer_id' => $customer->id,
                    'currency'    => $currency,
                    'description' => $desc,
                    'subs_fix'    => 1,
                ]);
                $registrationId = $this->db->insert_id;

                // Add field values of registration
                foreach ($field_label as $key => $field) {
                    if ($field_type[$key] != 'submit' && $field_type[$key] != 'total' &&
                        $field_type[$key] != 'priceoptions') {
                        $value = isset($_POST['form_' . $postId . '_field_' . $key]) ?
                            $_POST['form_' . $postId . '_field_' . $key] : '';
                        if ($field_type[$key] === 'payment_methods') {
                            $value = $_POST['rfmp_payment_method_' . $postId];
                        }

                        $this->db->insert($this->mollieForms->getRegistrationFieldsTable(), [
                            'registration_id' => $registrationId,
                            'field'           => $field,
                            'value'           => $value,
                            'type'            => $field_type[$key],
                        ]);
                    }
                }

                // Insert price options for registration
                foreach ($priceOptions as $priceOption) {
                    $price = $priceOption['option']->price_type == 'open' ?
                        (isset($_POST['rfmp_amount_' . $postId]) ?
                            str_replace(',', '.', $_POST['rfmp_amount_' . $postId]) : 0) :
                        $priceOption['option']->price;

                    $this->db->insert($this->mollieForms->getRegistrationPriceOptionsTable(), [
                        'post_id'         => $postId,
                        'registration_id' => $registrationId,
                        'price_option_id' => $priceOption['option']->id,
                        'description'     => $priceOption['option']->description,
                        'quantity'        => $priceOption['quantity'],
                        'currency'        => $currency,
                        'price'           => $price,
                        'price_type'      => $priceOption['option']->price_type,
                        'vat'             => $priceOption['option']->vat,
                        'frequency'       => $priceOption['option']->frequency,
                        'frequency_value' => $priceOption['option']->frequency_value,
                        'times'           => $priceOption['option']->times,
                    ]);
                }

                if ($paymentCosts > 0) {
                    $this->db->insert($this->mollieForms->getRegistrationPriceOptionsTable(), [
                        'post_id'         => $postId,
                        'registration_id' => $registrationId,
                        'description'     => __('Payment costs', 'mollie-forms'),
                        'quantity'        => 1,
                        'currency'        => $currency,
                        'price'           => $paymentCosts,
                        'price_type'      => 'fixed',
                        'vat'             => 21,
                        'frequency'       => 'once',
                    ]);
                }

                if (isset($discountAmount) && $discountAmount > 0) {
                    $this->db->insert($this->mollieForms->getRegistrationPriceOptionsTable(), [
                        'post_id'         => $postId,
                        'registration_id' => $registrationId,
                        'description'     => __('Discount', 'mollie-forms'),
                        'quantity'        => 1,
                        'currency'        => $currency,
                        'price'           => '-' . $discountAmount,
                        'price_type'      => 'fixed',
                        'vat'             => $vatRateFirstPriceOption,
                        'frequency'       => 'once',
                    ]);
                }

                if (isset($shippingPrice) && $shippingPrice > 0) {
                    $this->db->insert($this->mollieForms->getRegistrationPriceOptionsTable(), [
                        'post_id'         => $postId,
                        'registration_id' => $registrationId,
                        'description'     => __('Shipping costs', 'mollie-forms'),
                        'quantity'        => 1,
                        'currency'        => $currency,
                        'price'           => $shippingPrice,
                        'price_type'      => 'fixed',
                        'vat'             => 21,
                        'frequency'       => 'once',
                    ]);
                }

                // update registration
                $this->db->update($this->mollieForms->getRegistrationsTable(), [
                    'total_price' => $totalPrice,
                    'total_vat'   => $totalVat,
                ], [
                    'ID' => $registrationId,
                ]);


                if ($apiType == 'orders') {
                    // create Mollie order

                    $addressName = explode(' ', $customer->name, 2);

                    $addressSN      = trim($_POST['form_' . $postId . '_field_' .
                                                  array_search('address', $field_type)]);
                    $addressPC      = trim($_POST['form_' . $postId . '_field_' .
                                                  array_search('postalCode', $field_type)]);
                    $addressCity    = trim($_POST['form_' . $postId . '_field_' . array_search('city', $field_type)]);
                    $addressCountry = trim($_POST['form_' . $postId . '_field_' .
                                                  array_search('country', $field_type)]);


                    $orderLines = [];
                    foreach ($priceOptions as $priceOption) {

                        $unitPrice   = $priceOption['option']->price_type == 'open' ?
                            (isset($_POST['rfmp_amount_' . $postId]) ?
                                str_replace(',', '.', $_POST['rfmp_amount_' . $postId]) : 0) :
                            $priceOption['option']->price;
                        $totalAmount = ((float) $unitPrice) * $priceOption['quantity'];
                        $vatRate     = $priceOption['option']->vat ?: 21;

                        if ($vatSetting === 'excl') {
                            $vatAmount    = $totalAmount * ($vatRate / 100);
	                        $unitPrice   += ((float) $unitPrice) * ($vatRate / 100);
                            $totalAmount += $vatAmount;
                        } else {
                            $vatAmount = $totalAmount * ($vatRate / ($vatRate + 100));
                        }

                        $orderLines[] = [
                            'name'        => $priceOption['option']->description,
                            'quantity'    => $priceOption['quantity'],
                            'unitPrice'   => [
                                'currency' => $currency,
                                'value'    => number_format($unitPrice, $decimals, '.', ''),
                            ],
                            'totalAmount' => [
                                'currency' => $currency,
                                'value'    => number_format($totalAmount, $decimals, '.', ''),
                            ],
                            'vatRate'     => number_format($vatRate, 2, '.', ''),
                            'vatAmount'   => [
                                'currency' => $currency,
                                'value'    => number_format($vatAmount, $decimals, '.', ''),
                            ],
                        ];
                    }

                    if ($paymentCosts > 0) {
                        if ($vatSetting == 'excl') {
                            $paymentVat   = ((float) $paymentCosts) * 0.21;
                            $paymentCosts += $paymentVat;
                        } else {
                            $paymentVat = ((float) $paymentCosts) * (21 / 121);
                        }

                        $orderLines[] = [
                            'type'        => 'surcharge',
                            'name'        => __('Payment costs', 'mollie-forms'),
                            'quantity'    => 1,
                            'unitPrice'   => [
                                'currency' => $currency,
                                'value'    => number_format($paymentCosts, $decimals, '.', ''),
                            ],
                            'totalAmount' => [
                                'currency' => $currency,
                                'value'    => number_format($paymentCosts, $decimals, '.', ''),
                            ],
                            'vatRate'     => '21.00',
                            'vatAmount'   => [
                                'currency' => $currency,
                                'value'    => number_format($paymentVat, $decimals, '.', ''),
                            ],
                        ];
                    }

                    if (isset($shippingPrice) && $shippingPrice > 0) {
                        if ($vatSetting == 'excl') {
                            $shippingVat   = $shippingPrice * 0.21;
                            $shippingPrice += $shippingVat;
                        } else {
                            $shippingVat = $shippingPrice * (21 / 121);
                        }

                        $orderLines[] = [
                            'type'        => 'shipping_fee',
                            'name'        => __('Shipping costs', 'mollie-forms'),
                            'quantity'    => 1,
                            'unitPrice'   => [
                                'currency' => $currency,
                                'value'    => number_format($shippingPrice, $decimals, '.', ''),
                            ],
                            'totalAmount' => [
                                'currency' => $currency,
                                'value'    => number_format($shippingPrice, $decimals, '.', ''),
                            ],
                            'vatRate'     => '21.00',
                            'vatAmount'   => [
                                'currency' => $currency,
                                'value'    => number_format($shippingVat, $decimals, '.', ''),
                            ],
                        ];
                    }

                    if (isset($discountAmount, $discountVat) && $discountAmount > 0) {
                        $orderLines[] = [
                            'type'        => 'discount',
                            'name'        => __('Discount', 'mollie-forms'),
                            'quantity'    => 1,
                            'unitPrice'   => [
                                'currency' => $currency,
                                'value'    => '-' . number_format($discountAmount, $decimals, '.', ''),
                            ],
                            'totalAmount' => [
                                'currency' => $currency,
                                'value'    => '-' . number_format($discountAmount, $decimals, '.', ''),
                            ],
                            'vatRate'     => number_format($vatRateFirstPriceOption, 2, '.'),
                            'vatAmount'   => [
                                'currency' => $currency,
                                'value'    => '-' . number_format($discountVat, $decimals, '.', ''),
                            ],
                        ];
                    }

                    $orderData = [
                        'amount'         => [
                            'currency' => $currency,
                            'value'    => number_format($totalPrice, $decimals, '.', ''),
                        ],
                        'orderNumber'    => $rfmpId,
                        'lines'          => $orderLines,
                        'billingAddress' => [
                            'givenName'       => isset($addressName[0]) ? $addressName[0] : '',
                            'familyName'      => isset($addressName[1]) ? $addressName[1] : '',
                            'email'           => $customer->email,
                            'streetAndNumber' => $addressSN,
                            'postalCode'      => $addressPC,
                            'city'            => $addressCity,
                            'country'         => $addressCountry,
                        ],
                        'redirectUrl'    => $redirect . 'payment=' . $rfmpId,
                        'webhookUrl'     => $webhook,
                        'locale'         => $locale ?: 'nl_NL',
                        'method'         => $paymentMethod,
                        'metadata'       => [
                            'rfmp_id' => $rfmpId,
                        ],
                    ];

                    $orderData['payment']['customerId'] = $customer->id;

                    // first payment for subscription
                    if ($recurring) {
                        $orderData['payment']['sequenceType'] = 'first';
                        $orderData['webhookUrl']              = $webhook . '&first=' . $registrationId;
                    }

                    $order = $mollie->post('orders', $orderData);

                    do_action('rfmp_order_created', $postId, $order);

                    // create registration payment
                    $this->db->insert($this->mollieForms->getPaymentsTable(), [
                        'created_at'      => current_time('mysql', 1),
                        'registration_id' => $registrationId,
                        'payment_id'      => $order->id,
                        'payment_method'  => $paymentMethod,
                        'payment_mode'    => $order->mode,
                        'payment_status'  => 'open',
                        'currency'        => $order->amount->currency,
                        'amount'          => $order->amount->value,
                        'rfmp_id'         => $rfmpId,
                    ]);

                    wp_redirect($order->_links->checkout->href);
                    exit;
                } else {
                    // create Mollie payment
                    $paymentData = [
                        'amount'      => [
                            'currency' => $currency,
                            'value'    => number_format($totalPrice, $decimals, '.', ''),
                        ],
                        'description' => $desc,
                        'method'      => $paymentMethod,
                        'locale'      => $locale,
                        'redirectUrl' => $redirect . 'payment=' . $rfmpId,
                        'webhookUrl'  => $webhook,
                        'customerId'  => $customer->id,
                        'metadata'    => [
                            'rfmp_id' => $rfmpId,
                        ],
                    ];

                    // first payment for subscription
                    if ($recurring) {
                        $paymentData['sequenceType'] = 'first';
                        $paymentData['webhookUrl']   = $webhook . '&first=' . $registrationId;
                    }

                    $payment = $mollie->post('payments', $paymentData);

                    do_action('rfmp_payment_created', $postId, $payment);

                    // create registration payment
                    $this->db->insert($this->mollieForms->getPaymentsTable(), [
                        'created_at'      => current_time('mysql', 1),
                        'registration_id' => $registrationId,
                        'payment_id'      => $payment->id,
                        'payment_method'  => $payment->method,
                        'payment_mode'    => $payment->mode,
                        'payment_status'  => $payment->status,
                        'currency'        => $payment->amount->currency,
                        'amount'          => $payment->amount->value,
                        'rfmp_id'         => $rfmpId,
                    ]);

                    wp_redirect($payment->_links->checkout->href);
                    exit;
                }
            }

        } catch (Exception $e) {
            echo '<p style="color: red">' . $e->getMessage() . ' <a href="javascript: window.history.go(-1)">' . __('Go back', 'mollie-forms') . '</a></p>';

            if (isset($registrationId)) {
                // an error occurred, delete registration
                $this->db->delete($this->mollieForms->getRegistrationsTable(), [
                    'ID' => $registrationId,
                ]);
            }

            if (isset($customerId)) {
                // an error occurred, delete customer
                if (isset($mollie) && isset($customer) && isset($customer->id)) {
                    try {
                        $mollie->delete('customers/' . $customer->id);
                    } catch (Exception $e) {
                        // ignore
                    }
                }

                $this->db->delete($this->mollieForms->getCustomersTable(), [
                    'ID' => $customerId,
                ]);
            }
        }
    }

    /**
     * @param $postId
     * @param $rfmpId
     *
     * @return string
     */
    private function processRedirect($postId, $rfmpId)
    {
        $successClass    = get_post_meta($postId, '_rfmp_class_success', true);
        $errorClass      = get_post_meta($postId, '_rfmp_class_error', true);
        $successMessage  = get_post_meta($postId, '_rfmp_msg_success', true);
        $errorMessage    = get_post_meta($postId, '_rfmp_msg_error', true);
        $afterPayment    = get_post_meta($postId, '_rfmp_after_payment', true);
        $successRedirect = get_post_meta($postId, '_rfmp_redirect_success', true);
        $errorRedirect   = get_post_meta($postId, '_rfmp_redirect_error', true);

        $payment      = $this->db->get_row("SELECT * FROM {$this->mollieForms->getPaymentsTable()} WHERE rfmp_id='" .
                                           esc_sql($rfmpId) . "'");
        $registration = $this->db->get_row("SELECT * FROM {$this->mollieForms->getRegistrationsTable()} WHERE id='" .
                                           esc_sql($payment->registration_id) . "'");
        if ($payment == null || $registration == null) {
            echo '<p class="' . esc_attr($errorClass) . '">' . esc_html__('No payment found', 'mollie-forms') . '</p>';
        } elseif ($registration->post_id == $postId) {
            if ($payment->payment_status == 'paid' || $payment->payment_status == 'authorized') {
                if ($afterPayment == 'redirect') {
                    return wp_redirect($successRedirect);
                } else {
                    return '<p class="' . esc_attr($successClass) . '">' . esc_html($successMessage) . '</p>';
                }
            } elseif ($payment->payment_status != 'open') {
                if ($afterPayment == 'redirect') {
                    return wp_redirect($errorRedirect);
                } else {
                    return '<p class="' . esc_attr($errorClass) . '">' . esc_html($errorMessage) . '</p>';
                }
            }
        }
    }

}
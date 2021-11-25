<?php
/**
 * @var object $method
 * @var string $currency
 */
?>

<input type="hidden" value="0" name="rfmp_payment_method[<?php echo $method->id ?>]">
<label>
    <input type="checkbox"
           name="rfmp_payment_method[<?php echo $method->id ?>]"
            <?php echo isset($active[$method->id]) && $active[$method->id] ? 'checked' : '' ?>
           value="1">
    <img style="vertical-align:middle;display:inline-block;width:25px;"
         src="<?php echo esc_url($method->image->svg) ?>">

    <?php echo esc_html($method->description) ?>
</label>

<br>

<?php echo esc_html_e('Surcharge:', 'mollie-forms') ?>

<span style="font-size: 14px;"><?php echo $this->helpers->getCurrencySymbol($currency ?: 'EUR') ?></span>
<input type="number" step="any" min="0" name="rfmp_payment_method_fixed[<?php echo $method->id ?>]"
       value="<?php echo esc_attr($fixed[$method->id] ?? '') ?>" style="width: 70px;">

<span style="font-size: 14px;">+</span>

<input type="number" step="any" min="0" name="rfmp_payment_method_variable[<?php echo $method->id ?>]"
         value="<?php echo esc_attr($variable[$method->id] ?? '') ?>" style="width: 50px;">
<span style="font-size: 14px;">%</span>

<br>
<hr>
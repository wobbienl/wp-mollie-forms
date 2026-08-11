<?php if (!defined('ABSPATH')) exit;

/**
 * @var bool   $active
 * @var array  $fieldMap
 * @var array  $subMap
 * @var array  $rcurFields          RCUR customer field definitions: key => [label, required]
 * @var array  $formFields          Options for field selects: [{value,label}, ...]
 * @var array  $priceOptions        Recurring price options for this form
 * @var array  $mollieProfiles      Mollie profiles connected to the RCUR account
 * @var string $mollieProfilesError Error message if profiles could not be loaded
 * @var string $selectedProfile     Currently selected Mollie profile id for this form
 */
?>

<div class="inside">

    <p>
        <label>
            <input type="checkbox" name="rfmp_rcur_active" value="1" <?php checked($active); ?> id="rfmp_rcur_active">
            <strong><?php esc_html_e('Enable RCUR for this form', 'mollie-forms'); ?></strong>
        </label>
        <br>
        <span class="description">
            <?php esc_html_e('When active, customers are registered in RCUR after form submission. Recurring price options that are mapped below will create a subscription in RCUR instead of Mollie.', 'mollie-forms'); ?>
        </span>
    </p>

    <div id="rfmp_rcur_sections" <?php echo $active ? '' : 'style="display:none;"'; ?>>

        <h3 style="margin-top:1.5em;"><?php esc_html_e('Mollie profile', 'mollie-forms'); ?></h3>
        <?php if (!empty($mollieProfilesError)): ?>
            <p class="description" style="color:#d63638;">
                <?php echo esc_html(sprintf(__('Could not load Mollie profiles from RCUR: %s', 'mollie-forms'), $mollieProfilesError)); ?>
            </p>
        <?php elseif (empty($mollieProfiles)): ?>
            <p class="description">
                <?php esc_html_e('No Mollie profiles are connected to this RCUR account yet.', 'mollie-forms'); ?>
            </p>
            <?php if (trim($selectedProfile) !== ''): ?>
                <p class="description" style="color:#d63638;">
                    <?php echo esc_html(sprintf(__('The previously selected Mollie profile (%s) is no longer available in this RCUR account.', 'mollie-forms'), $selectedProfile)); ?>
                </p>
            <?php endif; ?>
        <?php else:
            $availableIds = [];
            foreach ($mollieProfiles as $profile) {
                if (isset($profile->id)) {
                    $availableIds[] = $profile->id;
                }
            }
            $selectedMissing = (trim($selectedProfile) !== '' && !in_array($selectedProfile, $availableIds, true));
        ?>
            <p class="description">
                <?php esc_html_e('Choose which Mollie profile RCUR subscriptions for this form are created under.', 'mollie-forms'); ?>
            </p>
            <?php if ($selectedMissing): ?>
                <p class="description" style="color:#d63638;">
                    <?php echo esc_html(sprintf(__('The selected Mollie profile (%s) is no longer available in this RCUR account. Please choose another profile.', 'mollie-forms'), $selectedProfile)); ?>
                </p>
            <?php endif; ?>
            <select name="rfmp_rcur_mollie_profile" style="min-width:320px;">
                <option value=""><?php esc_html_e('- Use global RCUR setting -', 'mollie-forms'); ?></option>
                <?php if ($selectedMissing): ?>
                    <option value="<?php echo esc_attr($selectedProfile); ?>" selected>
                        <?php echo esc_html(sprintf(__('%s (no longer available)', 'mollie-forms'), $selectedProfile)); ?>
                    </option>
                <?php endif; ?>
                <?php foreach ($mollieProfiles as $profile):
                    if (!isset($profile->id)) {
                        continue;
                    }
                    $pName   = isset($profile->name) && $profile->name !== '' ? $profile->name : $profile->id;
                    $pMode   = isset($profile->mode) ? $profile->mode : '';
                    $pStatus = isset($profile->status) ? $profile->status : '';
                    $pLabel  = sprintf('%s - %s (%s, %s)', $pName, $profile->id, $pMode, $pStatus);
                ?>
                    <option value="<?php echo esc_attr($profile->id); ?>" <?php selected($selectedProfile, $profile->id); ?>>
                        <?php echo esc_html($pLabel); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <h3 style="margin-top:1.5em;"><?php esc_html_e('Field mapping', 'mollie-forms'); ?></h3>
        <p class="description">
            <?php esc_html_e('Link each RCUR customer field to a form field. Required RCUR fields are marked with an asterisk.', 'mollie-forms'); ?>
        </p>

        <table class="widefat rfmp_table">
            <thead>
                <tr>
                    <th style="width:30%;"><?php esc_html_e('RCUR field', 'mollie-forms'); ?></th>
                    <th><?php esc_html_e('Form field', 'mollie-forms'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rcurFields as $rcurKey => $meta):
                    $label    = $meta[0];
                    $required = $meta[1];
                    $selected = isset($fieldMap[$rcurKey]) ? $fieldMap[$rcurKey] : '';
                ?>
                <tr>
                    <td>
                        <?php echo esc_html($label); ?><?php echo $required ? ' <span style="color:#d63638;">*</span>' : ''; ?>
                        <br>
                        <code style="font-size:11px;color:#666;"><?php echo esc_html($rcurKey); ?></code>
                    </td>
                    <td>
                        <select name="rfmp_rcur_field_map[<?php echo esc_attr($rcurKey); ?>]" style="min-width:260px;">
                            <option value=""><?php esc_html_e('- Not mapped -', 'mollie-forms'); ?></option>
                            <?php foreach ($formFields as $opt): ?>
                                <option value="<?php echo esc_attr($opt['value']); ?>" <?php selected($selected, $opt['value']); ?>>
                                    <?php echo esc_html($opt['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 style="margin-top:1.5em;"><?php esc_html_e('Subscription mapping', 'mollie-forms'); ?></h3>

        <?php if (empty($priceOptions)): ?>
            <p class="description">
                <?php esc_html_e('No recurring price options configured for this form. Add a price option with a frequency other than “Once” to map it to an RCUR subscription.', 'mollie-forms'); ?>
            </p>
        <?php else: ?>
            <p class="description">
                <?php esc_html_e('Enable an option to create an RCUR subscription instead of a Mollie subscription when that price option is purchased.', 'mollie-forms'); ?>
            </p>
            <table class="widefat rfmp_table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Enabled', 'mollie-forms'); ?></th>
                        <th><?php esc_html_e('Price option', 'mollie-forms'); ?></th>
                        <th><?php esc_html_e('RCUR name', 'mollie-forms'); ?></th>
                        <th><?php esc_html_e('Interval', 'mollie-forms'); ?></th>
                        <th><?php esc_html_e('Every', 'mollie-forms'); ?></th>
                        <th><?php esc_html_e('Day', 'mollie-forms'); ?></th>
                        <th><?php esc_html_e('Times', 'mollie-forms'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($priceOptions as $po):
                    $cfg            = isset($subMap[$po->id]) && is_array($subMap[$po->id]) ? $subMap[$po->id] : [];
                    $enabled        = !empty($cfg['enabled']);
                    $name           = isset($cfg['name']) && $cfg['name'] !== '' ? $cfg['name'] : $po->description;
                    $intervalGuess  = ['days' => 'day', 'weeks' => 'week', 'months' => 'month'];
                    $interval       = isset($cfg['interval']) && $cfg['interval'] !== ''
                        ? $cfg['interval']
                        : (isset($intervalGuess[$po->frequency]) ? $intervalGuess[$po->frequency] : 'month');
                    $intervalAmount = isset($cfg['interval_amount']) && $cfg['interval_amount'] !== ''
                        ? $cfg['interval_amount']
                        : (int) $po->frequency_value;
                    $day            = isset($cfg['day']) ? $cfg['day'] : '';
                    $times          = isset($cfg['times']) && $cfg['times'] !== ''
                        ? $cfg['times']
                        : (int) $po->times;
                ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="rfmp_rcur_sub_map[<?php echo esc_attr($po->id); ?>][enabled]" value="1" <?php checked($enabled); ?>>
                        </td>
                        <td>
                            <?php echo esc_html($po->description); ?><br>
                            <small style="color:#666;">
                                <?php echo esc_html(number_format((float) $po->price, 2, ',', '.')); ?>
                                &middot;
                                <?php echo esc_html($po->frequency_value . ' ' . $po->frequency); ?>
                            </small>
                        </td>
                        <td>
                            <input type="text" name="rfmp_rcur_sub_map[<?php echo esc_attr($po->id); ?>][name]" value="<?php echo esc_attr($name); ?>" style="width:100%;">
                        </td>
                        <td>
                            <select name="rfmp_rcur_sub_map[<?php echo esc_attr($po->id); ?>][interval]">
                                <?php foreach (['day' => __('Day', 'mollie-forms'), 'week' => __('Week', 'mollie-forms'), 'month' => __('Month', 'mollie-forms'), 'year' => __('Year', 'mollie-forms')] as $iv => $ivLabel): ?>
                                    <option value="<?php echo esc_attr($iv); ?>" <?php selected($interval, $iv); ?>><?php echo esc_html($ivLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" min="1" style="width:60px;" name="rfmp_rcur_sub_map[<?php echo esc_attr($po->id); ?>][interval_amount]" value="<?php echo esc_attr($intervalAmount); ?>">
                        </td>
                        <td>
                            <input type="number" min="1" max="31" style="width:60px;" name="rfmp_rcur_sub_map[<?php echo esc_attr($po->id); ?>][day]" value="<?php echo esc_attr($day); ?>" placeholder="-">
                        </td>
                        <td>
                            <input type="number" min="0" style="width:60px;" name="rfmp_rcur_sub_map[<?php echo esc_attr($po->id); ?>][times]" value="<?php echo esc_attr($times); ?>" placeholder="∞">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

</div>

<script>
    (function () {
        var toggle = document.getElementById('rfmp_rcur_active');
        var box    = document.getElementById('rfmp_rcur_sections');
        if (toggle && box) {
            toggle.addEventListener('change', function () {
                box.style.display = this.checked ? '' : 'none';
            });
        }
    })();
</script>

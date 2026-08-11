<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap">
    <h1><?php esc_html_e('RCUR integration', 'mollie-forms'); ?></h1>

    <p>
        <?php esc_html_e('Connect Mollie Forms with RCUR to register customers and manage recurring subscriptions through RCUR instead of Mollie.', 'mollie-forms'); ?>
        <a href="https://rcur.app" target="_blank" rel="noopener">rcur.app</a>
    </p>

    <form action="options.php" method="post">
        <?php settings_fields('mollie_forms_rcur'); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="mollie_forms_rcur_enabled"><?php esc_html_e('Enable RCUR integration', 'mollie-forms'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr(\MollieForms\Integrations\Rcur\RcurIntegration::OPTION_ENABLED); ?>" id="mollie_forms_rcur_enabled" value="1" <?php checked($enabled); ?>>
                            <?php esc_html_e('When enabled, each form gets an RCUR tab where you can map fields and recurring price options.', 'mollie-forms'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mollie_forms_rcur_api_key"><?php esc_html_e('RCUR API key', 'mollie-forms'); ?></label>
                    </th>
                    <td>
                        <input type="text" class="regular-text" name="<?php echo esc_attr(\MollieForms\Integrations\Rcur\RcurIntegration::OPTION_API_KEY); ?>" id="mollie_forms_rcur_api_key" value="<?php echo esc_attr($apiKey); ?>" autocomplete="off">
                        <p class="description">
                            <?php esc_html_e('Create an API key on the integrations page of your RCUR account.', 'mollie-forms'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="mollie_forms_rcur_mollie_profile_id"><?php esc_html_e('Mollie profile', 'mollie-forms'); ?></label>
                    </th>
                    <td>
                        <?php if (trim($apiKey) === ''): ?>
                            <p class="description">
                                <?php esc_html_e('Enter and save your RCUR API key first to load the connected Mollie profiles.', 'mollie-forms'); ?>
                            </p>
                        <?php elseif ($profilesError !== ''): ?>
                            <p class="description" style="color:#d63638;">
                                <?php echo esc_html(sprintf(__('Could not load Mollie profiles from RCUR: %s', 'mollie-forms'), $profilesError)); ?>
                            </p>
                        <?php elseif (empty($profiles)): ?>
                            <p class="description">
                                <?php esc_html_e('No Mollie profiles are connected to this RCUR account yet. Connect Mollie in your RCUR account first.', 'mollie-forms'); ?>
                            </p>
                            <?php if (trim($profileId) !== ''): ?>
                                <p class="description" style="color:#d63638;">
                                    <?php echo esc_html(sprintf(__('The previously selected Mollie profile (%s) is no longer available in this RCUR account.', 'mollie-forms'), $profileId)); ?>
                                </p>
                            <?php endif; ?>
                        <?php else:
                            $availableIds = [];
                            foreach ($profiles as $profile) {
                                if (isset($profile->id)) {
                                    $availableIds[] = $profile->id;
                                }
                            }
                            $selectedMissing = (trim($profileId) !== '' && !in_array($profileId, $availableIds, true));
                        ?>
                            <?php if ($selectedMissing): ?>
                                <p class="description" style="color:#d63638;">
                                    <?php echo esc_html(sprintf(__('The selected Mollie profile (%s) is no longer available in this RCUR account. Please choose another profile.', 'mollie-forms'), $profileId)); ?>
                                </p>
                            <?php endif; ?>
                            <select name="<?php echo esc_attr(\MollieForms\Integrations\Rcur\RcurIntegration::OPTION_PROFILE_ID); ?>" id="mollie_forms_rcur_mollie_profile_id">
                                <option value=""><?php esc_html_e('- Select a Mollie profile -', 'mollie-forms'); ?></option>
                                <?php if ($selectedMissing): ?>
                                    <option value="<?php echo esc_attr($profileId); ?>" selected>
                                        <?php echo esc_html(sprintf(__('%s (no longer available)', 'mollie-forms'), $profileId)); ?>
                                    </option>
                                <?php endif; ?>
                                <?php foreach ($profiles as $profile):
                                    if (!isset($profile->id)) {
                                        continue;
                                    }
                                    $name   = isset($profile->name) && $profile->name !== '' ? $profile->name : $profile->id;
                                    $mode   = isset($profile->mode) ? $profile->mode : '';
                                    $status = isset($profile->status) ? $profile->status : '';
                                    $label  = sprintf('%s - %s (%s, %s)', $name, $profile->id, $mode, $status);
                                ?>
                                    <option value="<?php echo esc_attr($profile->id); ?>" <?php selected($profileId, $profile->id); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <a href="<?php echo esc_url(wp_nonce_url(add_query_arg('rcur_refresh', '1'), 'mollie_forms_rcur_refresh')); ?>" class="button">
                                <?php esc_html_e('Refresh profiles', 'mollie-forms'); ?>
                            </a>
                            <p class="description">
                                <?php esc_html_e('Choose the default Mollie profile RCUR subscriptions are created under. Individual forms can override this in their RCUR tab. The list is cached for 10 minutes; use “Refresh profiles” after connecting a new profile in RCUR.', 'mollie-forms'); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(); ?>
    </form>
</div>

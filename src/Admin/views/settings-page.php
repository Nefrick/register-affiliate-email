<?php
/**
 * Settings Page Template
 *
 * @package RegisterAffiliateEmail\Admin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
$services = get_posts([
    'post_type' => 'rae_service',
    'posts_per_page' => -1,
    'post_status' => 'publish'
]);
$available_templates = \RegisterAffiliateEmail\Frontend\TemplateManager::getAvailableTemplates();
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('rae_messages'); ?>

    <form method="post" action="">
        <?php wp_nonce_field('rae_settings_nonce'); ?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="rae_input_placeholder">
                            <?php _e('Input Placeholder', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            id="rae_input_placeholder" 
                            name="rae_input_placeholder" 
                            value="<?php echo esc_attr($settings['input_placeholder']); ?>" 
                            class="regular-text"
                        />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rae_button_text">
                            <?php _e('Button Text', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <input 
                            type="text" 
                            id="rae_button_text" 
                            name="rae_button_text" 
                            value="<?php echo esc_attr($settings['button_text']); ?>" 
                            class="regular-text"
                        />
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rae_form_heading">
                            <?php _e('Form Heading', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea 
                            id="rae_form_heading" 
                            name="rae_form_heading" 
                            class="large-text" 
                            rows="2"
                        ><?php echo esc_textarea($settings['form_heading'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php _e('Main heading text displayed above the form. HTML allowed.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rae_form_subheading">
                            <?php _e('Form Subheading', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea 
                            id="rae_form_subheading" 
                            name="rae_form_subheading" 
                            class="large-text" 
                            rows="3"
                        ><?php echo esc_textarea($settings['form_subheading'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php _e('Subheading text displayed below the main heading. HTML allowed.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rae_success_message">
                            <?php _e('Success Message', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea 
                            id="rae_success_message" 
                            name="rae_success_message" 
                            class="large-text" 
                            rows="2"
                        ><?php echo esc_textarea($settings['success_message'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php _e('Message shown after successful subscription. HTML allowed.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label>
                            <?php _e('Agreement Checkbox', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <label>
                            <input 
                                type="checkbox" 
                                name="rae_show_agreement" 
                                value="1"
                                <?php checked($settings['show_agreement'] ?? false); ?>
                            />
                            <?php _e('Show agreement checkbox', 'register-affiliate-email'); ?>
                        </label>
                        <br><br>
                        <textarea 
                            name="rae_agreement_text" 
                            class="large-text" 
                            rows="3"
                            placeholder="<?php esc_attr_e('By subscribing, I accept the Terms...', 'register-affiliate-email'); ?>"
                        ><?php echo esc_textarea($settings['agreement_text'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php _e('Text for the agreement checkbox. HTML allowed.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rae_active_template">
                            <?php _e('Form Template', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <?php foreach ($available_templates as $slug => $name): ?>
                            <label style="display: block; margin-bottom: 8px;">
                                <input 
                                    type="radio" 
                                    name="rae_active_template" 
                                    value="<?php echo esc_attr($slug); ?>"
                                    <?php checked($settings['active_template'] ?? 'default', $slug); ?>
                                />
                                <?php echo esc_html($name); ?>
                            </label>
                        <?php endforeach; ?>
                        <p class="description">
                            <?php _e('Select which form template to use. Custom templates can be added to the /templates folder.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="rae_background_image">
                            <?php _e('Background Image', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <input 
                            type="hidden" 
                            id="rae_background_image" 
                            name="rae_background_image" 
                            value="<?php echo esc_url($settings['background_image']); ?>"
                        />
                        <button type="button" class="button rae-upload-image">
                            <?php _e('Upload Background', 'register-affiliate-email'); ?>
                        </button>
                        <button type="button" class="button rae-remove-image" style="<?php echo empty($settings['background_image']) ? 'display:none;' : ''; ?>">
                            <?php _e('Remove', 'register-affiliate-email'); ?>
                        </button>
                        <div class="rae-image-preview" style="margin-top: 10px;">
                            <?php if (!empty($settings['background_image'])): ?>
                                <img src="<?php echo esc_url($settings['background_image']); ?>" style="max-width: 300px; height: auto;" />
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label>
                            <?php _e('Enabled Services', 'register-affiliate-email'); ?>
                        </label>
                    </th>
                    <td>
                        <?php if (empty($services)): ?>
                            <p>
                                <?php _e('No services found.', 'register-affiliate-email'); ?>
                                <a href="<?php echo admin_url('post-new.php?post_type=rae_service'); ?>">
                                    <?php _e('Add a service', 'register-affiliate-email'); ?>
                                </a>
                            </p>
                        <?php else: ?>
                            <?php foreach ($services as $service): ?>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input 
                                        type="checkbox" 
                                        name="rae_enabled_services[]" 
                                        value="<?php echo esc_attr($service->ID); ?>"
                                        <?php checked(in_array($service->ID, $settings['enabled_services'])); ?>
                                    />
                                    <?php echo esc_html($service->post_title); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php _e('Shortcode', 'register-affiliate-email'); ?></label>
                    </th>
                    <td>
                        <code>[register_affiliate_email]</code>
                        <p class="description">
                            <?php _e('Use this shortcode to display the subscription form.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Save Settings', 'register-affiliate-email'), 'primary', 'rae_save_settings'); ?>
    </form>
</div>

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

// Get version info
$current_version = RAE_VERSION;
$update_checker = new \RegisterAffiliateEmail\Updates\UpdateChecker();
$remote_version = $update_checker->getRemoteVersionPublic();
$update_available = $remote_version && version_compare($current_version, $remote_version, '<');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('rae_messages'); ?>

    <?php if (isset($_GET['update-checked'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p>✓ <?php _e('Update check completed! Cache cleared.', 'register-affiliate-email'); ?></p>
        </div>
    <?php endif; ?>

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
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <code id="rae-shortcode" style="font-size: 14px; padding: 5px 10px; background: #f0f0f1; border-radius: 3px;">[register_affiliate_email]</code>
                            <button type="button" class="button" id="rae-copy-shortcode" onclick="raesCopyShortcode()">
                                📋 <?php _e('Copy', 'register-affiliate-email'); ?>
                            </button>
                            <span id="rae-copy-feedback" style="color: #00a32a; display: none; font-weight: bold;">✓ Copied!</span>
                        </div>
                        <p class="description" style="margin-top: 10px;">
                            <?php _e('Use this shortcode to display the subscription form.', 'register-affiliate-email'); ?>
                        </p>
                        <script>
                        function raesCopyShortcode() {
                            const shortcode = '[register_affiliate_email]';
                            const feedback = document.getElementById('rae-copy-feedback');
                            
                            // Modern browsers
                            if (navigator.clipboard && window.isSecureContext) {
                                navigator.clipboard.writeText(shortcode).then(() => {
                                    showCopyFeedback(feedback);
                                });
                            } else {
                                // Fallback for older browsers
                                const textArea = document.createElement('textarea');
                                textArea.value = shortcode;
                                textArea.style.position = 'fixed';
                                textArea.style.left = '-999999px';
                                document.body.appendChild(textArea);
                                textArea.select();
                                try {
                                    document.execCommand('copy');
                                    showCopyFeedback(feedback);
                                } catch (err) {
                                    console.error('Copy failed:', err);
                                }
                                document.body.removeChild(textArea);
                            }
                        }
                        
                        function showCopyFeedback(element) {
                            element.style.display = 'inline';
                            setTimeout(() => {
                                element.style.display = 'none';
                            }, 2000);
                        }
                        </script>
                    </td>
                </tr>

                <!-- Version Check Section -->
                <tr>
                    <th scope="row" colspan="2" style="padding-top: 30px;">
                        <h2 style="margin: 0; border-top: 1px solid #ddd; padding-top: 20px;">
                            📦 <?php _e('Plugin Version & Updates', 'register-affiliate-email'); ?>
                        </h2>
                    </th>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Current Version', 'register-affiliate-email'); ?></label>
                    </th>
                    <td>
                        <code style="font-size: 14px; padding: 5px 10px; background: #f0f0f1; border-radius: 3px;">
                            <?php echo esc_html($current_version); ?>
                        </code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Latest on GitHub', 'register-affiliate-email'); ?></label>
                    </th>
                    <td>
                        <?php if ($remote_version) : ?>
                            <code style="font-size: 14px; padding: 5px 10px; background: #f0f0f1; border-radius: 3px;">
                                <?php echo esc_html($remote_version); ?>
                            </code>
                            <?php if ($update_available) : ?>
                                <span style="color: #d63638; font-weight: bold; margin-left: 10px;">
                                    ⚠️ <?php _e('Update available!', 'register-affiliate-email'); ?>
                                </span>
                            <?php else : ?>
                                <span style="color: #00a32a; margin-left: 10px;">
                                    ✓ <?php _e('Up to date', 'register-affiliate-email'); ?>
                                </span>
                            <?php endif; ?>
                        <?php else : ?>
                            <code style="font-size: 14px; padding: 5px 10px; background: #f0f0f1; border-radius: 3px;">—</code>
                            <span style="color: #999; margin-left: 10px;">
                                <?php _e('Unable to check', 'register-affiliate-email'); ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label><?php _e('Actions', 'register-affiliate-email'); ?></label>
                    </th>
                    <td>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=register-affiliate-email&rae_check_update=1')); ?>" 
                           class="button" style="margin-right: 10px;">
                            🔄 <?php _e('Check for Updates', 'register-affiliate-email'); ?>
                        </a>
                        <a href="https://github.com/Nefrick/register-affiliate-email/releases" 
                           class="button" target="_blank">
                            📋 <?php _e('View Changelog', 'register-affiliate-email'); ?>
                        </a>
                        <p class="description" style="margin-top: 10px;">
                            <?php _e('Check GitHub for new versions and view release notes.', 'register-affiliate-email'); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button(__('Save Settings', 'register-affiliate-email'), 'primary', 'rae_save_settings'); ?>
    </form>
</div>

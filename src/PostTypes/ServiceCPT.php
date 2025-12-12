<?php
/**
 * Email Service Custom Post Type
 *
 * @package RegisterAffiliateEmail\PostTypes
 */

namespace RegisterAffiliateEmail\PostTypes;

class ServiceCPT {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', [$this, 'register']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post_rae_service', [$this, 'saveMetaBoxes'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('wp_ajax_rae_load_service_fields', [$this, 'ajaxLoadServiceFields']);
    }

    /**
     * Register custom post type
     */
    public function register() {
        register_post_type('rae_service', [
            'labels' => [
                'name' => __('Email Services', 'register-affiliate-email'),
                'singular_name' => __('Email Service', 'register-affiliate-email'),
                'add_new' => __('Add New Service', 'register-affiliate-email'),
                'add_new_item' => __('Add New Email Service', 'register-affiliate-email'),
                'edit_item' => __('Edit Email Service', 'register-affiliate-email'),
                'new_item' => __('New Email Service', 'register-affiliate-email'),
                'view_item' => __('View Email Service', 'register-affiliate-email'),
                'search_items' => __('Search Email Services', 'register-affiliate-email'),
                'not_found' => __('No email services found', 'register-affiliate-email'),
                'not_found_in_trash' => __('No email services found in trash', 'register-affiliate-email'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        ]);
    }

    /**
     * Add meta boxes
     */
    public function addMetaBoxes() {
        add_meta_box(
            'rae_service_type',
            __('Service Type', 'register-affiliate-email'),
            [$this, 'renderServiceTypeMetaBox'],
            'rae_service',
            'side',
            'high'
        );

        add_meta_box(
            'rae_service_config',
            __('Service Configuration', 'register-affiliate-email'),
            [$this, 'renderConfigMetaBox'],
            'rae_service',
            'normal',
            'high'
        );
    }

    /**
     * Render service type selector
     */
    public function renderServiceTypeMetaBox($post) {
        wp_nonce_field('rae_service_type_nonce', 'rae_service_type_nonce');
        
        $config = get_post_meta($post->ID, '_rae_service_config', true);
        $config_array = !empty($config) ? json_decode($config, true) : [];
        $current_service = \RegisterAffiliateEmail\Services\ServiceTemplateManager::getServiceSlug($config_array);
        
        $available_services = \RegisterAffiliateEmail\Services\ServiceTemplateManager::getAvailableServices();
        
        ?>
        <p>
            <label for="rae_service_type">
                <strong><?php _e('Select Service:', 'register-affiliate-email'); ?></strong>
            </label>
        </p>
        <select id="rae_service_type" name="rae_service_type" style="width: 100%;">
            <option value=""><?php _e('-- Select Service --', 'register-affiliate-email'); ?></option>
            <?php foreach ($available_services as $slug => $service): ?>
                <option value="<?php echo esc_attr($slug); ?>" <?php selected($current_service, $slug); ?>>
                    <?php echo esc_html($service['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <?php if (!empty($current_service) && isset($available_services[$current_service])): ?>
            <p class="description" style="margin-top: 10px;">
                <?php echo esc_html($available_services[$current_service]['description']); ?>
            </p>
        <?php endif; ?>

        <p style="margin-top: 15px;">
            <button type="button" class="button button-secondary" id="rae-load-service-fields">
                <?php _e('Load Service Fields', 'register-affiliate-email'); ?>
            </button>
        </p>
        <?php
    }

    /**
     * Render configuration meta box
     */
    public function renderConfigMetaBox($post) {
        wp_nonce_field('rae_service_config_nonce', 'rae_service_config_nonce');
        
        $config_json = get_post_meta($post->ID, '_rae_service_config', true);
        $config = !empty($config_json) ? json_decode($config_json, true) : [];
        
        $service_slug = \RegisterAffiliateEmail\Services\ServiceTemplateManager::getServiceSlug($config);
        $field_values = \RegisterAffiliateEmail\Services\ServiceTemplateManager::extractFieldValues($config);
        
        ?>
        <div id="rae-service-fields-container">
            <?php if ($service_slug): ?>
                <?php
                $template = \RegisterAffiliateEmail\Services\ServiceTemplateManager::loadServiceTemplate($service_slug);
                if ($template && !empty($template['fields'])):
                ?>
                    <table class="form-table">
                        <tbody>
                            <?php foreach ($template['fields'] as $field): ?>
                                <?php 
                                $value = $field_values[$field['name']] ?? '';
                                echo \RegisterAffiliateEmail\Services\ServiceTemplateManager::renderField($field, $value);
                                ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php _e('No fields configured for this service.', 'register-affiliate-email'); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="description">
                    <?php _e('Please select a service type from the sidebar and click "Load Service Fields".', 'register-affiliate-email'); ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- Hidden field to store final JSON config -->
        <input type="hidden" id="rae_service_config_json" name="rae_service_config" value="<?php echo esc_attr($config_json); ?>" />
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f8f8; border-left: 4px solid #0073aa;">
            <p><strong><?php _e('Advanced: View Generated JSON', 'register-affiliate-email'); ?></strong></p>
            <button type="button" class="button button-small" id="rae-toggle-json">
                <?php _e('Show JSON', 'register-affiliate-email'); ?>
            </button>
            <pre id="rae-json-preview" style="display: none; background: white; padding: 10px; margin-top: 10px; overflow-x: auto; max-height: 300px;"><?php echo esc_html($config_json); ?></pre>
        </div>
        <?php
    }

    /**
     * Save meta boxes
     */
    public function saveMetaBoxes($post_id, $post) {
        // Check nonce
        if (!isset($_POST['rae_service_config_nonce']) || 
            !wp_verify_nonce($_POST['rae_service_config_nonce'], 'rae_service_config_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get service type
        $service_type = sanitize_text_field($_POST['rae_service_type'] ?? '');
        
        if (empty($service_type)) {
            return;
        }

        // Get field values
        $field_values = [];
        if (isset($_POST['rae_field']) && is_array($_POST['rae_field'])) {
            foreach ($_POST['rae_field'] as $key => $value) {
                $field_values[sanitize_key($key)] = wp_unslash($value);
            }
        }

        // Build configuration
        $config = \RegisterAffiliateEmail\Services\ServiceTemplateManager::buildConfig($service_type, $field_values);
        
        if ($config) {
            $config_json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            update_post_meta($post_id, '_rae_service_config', $config_json);
        }
    }

    /**
     * Enqueue admin scripts for service edit screen
     */
    public function enqueueAdminScripts($hook) {
        global $post;

        if (($hook === 'post.php' || $hook === 'post-new.php') && 
            isset($post) && $post->post_type === 'rae_service') {
            
            wp_enqueue_script(
                'rae-service-admin',
                plugin_dir_url(dirname(__DIR__)) . 'assets/service-admin.js',
                [],
                '1.0.0',
                true
            );

            wp_localize_script('rae-service-admin', 'raeServiceAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rae_service_fields'),
                'selectServiceText' => __('Please select a service type first.', 'register-affiliate-email'),
                'loadingText' => __('Loading...', 'register-affiliate-email'),
                'loadFieldsText' => __('Load Service Fields', 'register-affiliate-email'),
                'errorText' => __('Error loading service fields. Please try again.', 'register-affiliate-email'),
                'showJsonText' => __('Show JSON', 'register-affiliate-email'),
                'hideJsonText' => __('Hide JSON', 'register-affiliate-email'),
            ]);
        }
    }

    /**
     * AJAX handler to load service fields dynamically
     */
    public function ajaxLoadServiceFields() {
        check_ajax_referer('rae_service_fields', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'register-affiliate-email')]);
        }

        $service_slug = sanitize_text_field($_POST['service_slug'] ?? '');

        if (empty($service_slug)) {
            wp_send_json_error(['message' => __('No service selected.', 'register-affiliate-email')]);
        }

        $template = \RegisterAffiliateEmail\Services\ServiceTemplateManager::loadServiceTemplate($service_slug);

        if (!$template || empty($template['fields'])) {
            wp_send_json_error(['message' => __('Service template not found.', 'register-affiliate-email')]);
        }

        // Build HTML for fields
        ob_start();
        echo '<table class="form-table"><tbody>';
        foreach ($template['fields'] as $field) {
            echo \RegisterAffiliateEmail\Services\ServiceTemplateManager::renderField($field, '');
        }
        echo '</tbody></table>';
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'service_name' => $template['name'],
            'service_description' => $template['description']
        ]);
    }
}

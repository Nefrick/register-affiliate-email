<?php
/**
 * MetaBox for enabling subscription form on posts
 * @package RegisterAffiliateEmail\Admin
 */

namespace RegisterAffiliateEmail\Admin;

class MetaBox {
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post', [$this, 'saveMetaBox']);
    }

    public function addMetaBox($post_type) {
        $settings = Settings::getSettings();
        $enabled_types = $settings['enabled_post_types'] ?? ['post'];
        if (!in_array($post_type, $enabled_types)) return;
        add_meta_box(
            'rae_enable_form',
            __('Subscription Form', 'register-affiliate-email'),
            [$this, 'renderMetaBox'],
            $post_type,
            'side',
            'default'
        );
    }

    public function renderMetaBox($post) {
        $enabled = get_post_meta($post->ID, '_rae_enable_form', true);
        $selected_segment_id = get_post_meta($post->ID, '_rae_customerio_segment_id', true);
        $selected_segment_title = get_post_meta($post->ID, '_rae_customerio_segment_title', true);
        wp_nonce_field('rae_enable_form_nonce', 'rae_enable_form_nonce_field');
        ?>
        <label>
            <input type="checkbox" name="rae_enable_form" value="1" <?php checked($enabled); ?> />
            <?php _e('Show subscription form on this post', 'register-affiliate-email'); ?>
        </label>
        <p style="margin-top:10px;">
            <?php _e('Shortcode:', 'register-affiliate-email'); ?>
            <code style="background:#f0f0f1;padding:2px 6px;border-radius:3px;">[register_affiliate_email]</code>        
        </p>
        <?php
        // Если чекбокс активен, проверяем Customer.io
        if ($enabled) {
            $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
            $enabled_services = $settings['enabled_services'] ?? [];
            $customerio_service_id = null;
             
            foreach ($enabled_services as $service_id) {
                $config_json = get_post_meta($service_id, '_rae_service_config', true);
                $config = json_decode($config_json, true);
                // Новая логика для customerio
                $is_customerio = (
                    (isset($config['service_type']) && $config['service_type'] === 'customerio') ||
                    (isset($config['_service_slug']) && $config['_service_slug'] === 'customerio')
                );
                $api_key = $config['api_key'] ??
                    ($config['body']['api_key'] ??
                    ($config['_field_values']['api_key'] ?? ''));
                if ($is_customerio && !empty($api_key)) {
                    $customerio_service_id = $service_id;
                    break;
                }
            }
         
            if (!empty($customerio_service_id)) {
                $segments = get_post_meta($customerio_service_id, '_rae_customerio_segments', true);               
                if (!empty($segments)) {
                    $segments = is_string($segments) ? json_decode($segments, true) : $segments;
                    echo '<div style="margin-top:10px;"><strong>' . __('Customer.io Segment:', 'register-affiliate-email') . '</strong><br>';
                    echo '<select name="rae_customerio_segment_id" style="margin-top:8px; max-width:100%; width:auto; min-width:180px;">';
                    echo '<option value="">' . __('Empty', 'register-affiliate-email') . '</option>';
                    foreach ($segments as $segment) {
                        $seg_id = esc_attr($segment['id'] ?? '');
                        $seg_name = esc_html($segment['name'] ?? '-');
                        $selected = ($seg_id && $seg_id == $selected_segment_id) ? 'selected' : '';
                        echo "<option value=\"$seg_id\" data-title=\"$seg_name\" $selected>$seg_name ($seg_id)</option>";
                    }
                    echo '</select>';
                    if ($selected_segment_id && $selected_segment_title) {
                        echo '<p style="margin:6px 0 0 0;">' . __('Selected segment:', 'register-affiliate-email') . ' <strong>' . esc_html($selected_segment_title) . ' (' . esc_html($selected_segment_id) . ')</strong></p>';
                    }
                    echo '<p class="description">' . __('Select a segment for this post. Segment list is managed in the service config.', 'register-affiliate-email') . '</p>';
                    echo '</div>';
                } else {
                    echo '<div style="margin-top:10px;color:#d63638;">' . __('No segments found for this Customer.io service.', 'register-affiliate-email') . '</div>';
                }
            }
        }
    }

    public function saveMetaBox($post_id) {
        if (!isset($_POST['rae_enable_form_nonce_field']) || !wp_verify_nonce($_POST['rae_enable_form_nonce_field'], 'rae_enable_form_nonce')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (isset($_POST['rae_enable_form'])) {
            update_post_meta($post_id, '_rae_enable_form', 1);
        } else {
            delete_post_meta($post_id, '_rae_enable_form');
        }
        // Save selected Customer.io segment
        if (isset($_POST['rae_customerio_segment_id'])) {
            $seg_id = sanitize_text_field($_POST['rae_customerio_segment_id']);
            update_post_meta($post_id, '_rae_customerio_segment_id', $seg_id);
            // Also save segment title (name)
            if (!empty($seg_id)) {
                // Get segment title from Customer.io segments
                $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
                $enabled_services = $settings['enabled_services'] ?? [];
                $customerio_service_id = null;
                foreach ($enabled_services as $service_id) {
                    $config_json = get_post_meta($service_id, '_rae_service_config', true);
                    $config = json_decode($config_json, true);
                    // New logic for customerio
                    $is_customerio = (
                        (isset($config['service_type']) && $config['service_type'] === 'customerio') ||
                        (isset($config['_service_slug']) && $config['_service_slug'] === 'customerio')
                    );
                    $api_key = $config['api_key'] ??
                        ($config['body']['api_key'] ??
                        ($config['_field_values']['api_key'] ?? ''));
                    if ($is_customerio && !empty($api_key)) {
                        $customerio_service_id = $service_id;
                        break;
                    }
                }
                $seg_title = '';
                if (!empty($customerio_service_id)) {
                    $segments = get_post_meta($customerio_service_id, '_rae_customerio_segments', true);
                    $segments = is_string($segments) ? json_decode($segments, true) : $segments;
                    if (!empty($segments) && is_array($segments)) {
                        foreach ($segments as $segment) {
                            if (($segment['id'] ?? '') == $seg_id) {
                                $seg_title = $segment['name'] ?? '';
                                break;
                            }
                        }
                    }
                }
                update_post_meta($post_id, '_rae_customerio_segment_title', $seg_title);
            } else {
                delete_post_meta($post_id, '_rae_customerio_segment_title');
            }
        } else {
            delete_post_meta($post_id, '_rae_customerio_segment_id');
            delete_post_meta($post_id, '_rae_customerio_segment_title');
        }
    }
}

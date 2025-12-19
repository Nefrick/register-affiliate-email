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
    }
}

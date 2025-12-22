<?php
/**
 * Admin Menu Handler
 *
 * @package RegisterAffiliateEmail\Admin
 */

namespace RegisterAffiliateEmail\Admin;

class Menu {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'registerMenus']);
        add_action('admin_init', [$this, 'handleUpdateCheck']);
    }

    /**
     * Handle update check request
     */
    public function handleUpdateCheck() {
        if (!isset($_GET['rae_check_update']) || !current_user_can('manage_options')) {
            return;
        }

        // Clear cache and force check
        delete_transient('rae_remote_version');
        delete_site_transient('update_plugins');

        wp_redirect(admin_url('admin.php?page=register-affiliate-email&update-checked=1'));
        exit;
    }

    /**
     * Register admin menus
     */
    public function registerMenus() {
        // Main menu
        add_menu_page(
            __('Register Affiliate Email', 'register-affiliate-email'),
            __('Affiliate Email', 'register-affiliate-email'),
            'manage_options',
            'register-affiliate-email',
            [$this, 'renderMainPage'],
            'dashicons-email-alt',
            30
        );

        // Global Settings submenu
        add_submenu_page(
            'register-affiliate-email',
            __('Global Settings', 'register-affiliate-email'),
            __('Global Settings', 'register-affiliate-email'),
            'manage_options',
            'register-affiliate-email',
            [$this, 'renderMainPage']
        );

        // Services submenu (links to custom post type)
        add_submenu_page(
            'register-affiliate-email',
            __('Email Services', 'register-affiliate-email'),
            __('Email Services', 'register-affiliate-email'),
            'manage_options',
            'edit.php?post_type=rae_service'
        );
    }

    /**
     * Render main settings page
     */
    public function renderMainPage() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Check if form was submitted
        if (isset($_POST['rae_save_settings']) && check_admin_referer('rae_settings_nonce')) {
            $this->saveSettings();
        }

        include RAE_PLUGIN_DIR . 'src/Admin/views/settings-page.php';
    }

    /**
     * Save form settings
     */
    private function saveSettings() {
        $settings = [
            'enable_rate_limit' => isset($_POST['rae_enable_rate_limit']),
            'input_placeholder' => sanitize_text_field(wp_unslash($_POST['rae_input_placeholder'] ?? '')),
            'button_text' => sanitize_text_field(wp_unslash($_POST['rae_button_text'] ?? '')),
            'form_heading' => wp_kses_post(wp_unslash($_POST['rae_form_heading'] ?? '')),
            'form_subheading' => wp_kses_post(wp_unslash($_POST['rae_form_subheading'] ?? '')),
            'background_image' => esc_url_raw($_POST['rae_background_image'] ?? ''),
            'button_color' => isset($_POST['rae_button_color']) ? sanitize_hex_color($_POST['rae_button_color']) : '#0073aa',
            'show_agreement' => isset($_POST['rae_show_agreement']),
            'agreement_text' => wp_kses_post(wp_unslash($_POST['rae_agreement_text'] ?? '')),
            'success_message' => wp_kses_post(wp_unslash($_POST['rae_success_message'] ?? '')),
            'active_template' => sanitize_text_field($_POST['rae_active_template'] ?? 'default'),
            'enabled_services' => array_map('intval', $_POST['rae_enabled_services'] ?? []),
            'enabled_post_types' => isset($_POST['rae_enabled_post_types']) ? array_map('sanitize_text_field', (array) $_POST['rae_enabled_post_types']) : ['post'],
            'submission_limit' => isset($_POST['rae_submission_limit']) ? (int)$_POST['rae_submission_limit'] : 100,
            'submission_period' => isset($_POST['rae_submission_period']) && $_POST['rae_submission_period'] === 'day' ? 'day' : 'hour',
        ];

        update_option('rae_form_settings', $settings);
        
        add_settings_error(
            'rae_messages',
            'rae_message',
            __('Settings saved successfully.', 'register-affiliate-email'),
            'success'
        );
    }
}

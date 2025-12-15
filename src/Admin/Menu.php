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
            'input_placeholder' => sanitize_text_field(wp_unslash($_POST['rae_input_placeholder'] ?? '')),
            'button_text' => sanitize_text_field(wp_unslash($_POST['rae_button_text'] ?? '')),
            'form_heading' => wp_kses_post(wp_unslash($_POST['rae_form_heading'] ?? '')),
            'form_subheading' => wp_kses_post(wp_unslash($_POST['rae_form_subheading'] ?? '')),
            'background_image' => esc_url_raw($_POST['rae_background_image'] ?? ''),
            'show_agreement' => isset($_POST['rae_show_agreement']),
            'agreement_text' => wp_kses_post(wp_unslash($_POST['rae_agreement_text'] ?? '')),
            'success_message' => wp_kses_post(wp_unslash($_POST['rae_success_message'] ?? '')),
            'active_template' => sanitize_text_field($_POST['rae_active_template'] ?? 'default'),
            'enabled_services' => array_map('intval', $_POST['rae_enabled_services'] ?? [])
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

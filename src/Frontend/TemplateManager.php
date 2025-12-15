<?php
/**
 * Form Template Manager
 *
 * @package RegisterAffiliateEmail\Frontend
 */

namespace RegisterAffiliateEmail\Frontend;

class TemplateManager {
    /**
     * Get all available templates
     *
     * @return array Array of templates with slug => name
     */
    public static function getAvailableTemplates() {
        $templates = [
            'default' => __('Default Template', 'register-affiliate-email')
        ];

        $templates_dir = RAE_PLUGIN_DIR . 'templates/';
        
        if (!is_dir($templates_dir)) {
            return $templates;
        }

        $files = glob($templates_dir . '*.php');
        
        foreach ($files as $file) {
            $slug = basename($file, '.php');
            
            // Skip default template as it's already added
            if ($slug === 'default') {
                continue;
            }
            
            // Get template name from file header
            $file_data = get_file_data($file, [
                'name' => 'Template Name'
            ]);
            
            $name = !empty($file_data['name']) ? $file_data['name'] : ucfirst(str_replace('-', ' ', $slug));
            $templates[$slug] = $name;
        }

        return $templates;
    }

    /**
     * Get active template slug
     *
     * @return string
     */
    public static function getActiveTemplate() {
        $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
        return isset($settings['active_template']) ? $settings['active_template'] : 'default';
    }

    /**
     * Load template file
     *
     * @param string $template_slug Template slug
     * @param array $data Template data
     * @return string HTML output
     */
    public static function loadTemplate($template_slug, $data = []) {
        $template_file = RAE_PLUGIN_DIR . 'templates/' . $template_slug . '.php';
        
        // Fallback to default if template doesn't exist
        if (!file_exists($template_file)) {
            $template_file = RAE_PLUGIN_DIR . 'templates/default.php';
        }

        // Extract data for template
        extract($data);

        ob_start();
        include $template_file;
        return ob_get_clean();
    }
}

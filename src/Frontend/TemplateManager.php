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

        // Scan for subdirectories with template.php files
        $dirs = glob($templates_dir . '*', GLOB_ONLYDIR);
        
        foreach ($dirs as $dir) {
            $template_file = $dir . '/template.php';
            
            if (!file_exists($template_file)) {
                continue;
            }
            
            $slug = basename($dir);
            
            // Get template name from file header
            $file_data = get_file_data($template_file, [
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
        // Check for template in subdirectory structure first
        $template_file = RAE_PLUGIN_DIR . 'templates/' . $template_slug . '/template.php';
        
        // Fallback to old flat structure for default template
        if (!file_exists($template_file)) {
            $template_file = RAE_PLUGIN_DIR . 'templates/' . $template_slug . '.php';
        }
        
        // Final fallback to default
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

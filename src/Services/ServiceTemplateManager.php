<?php
/**
 * Service Template Manager
 *
 * Loads and manages service JSON templates
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

class ServiceTemplateManager {
    /**
     * Get all available service templates
     *
     * @return array Array of service templates
     */
    public static function getAvailableServices() {
        $services_dir = RAE_PLUGIN_DIR . 'services/';
        $services = [];

        if (!is_dir($services_dir)) {
            return $services;
        }

        $files = glob($services_dir . '*.json');

        foreach ($files as $file) {
            $service_data = self::loadServiceTemplate(basename($file, '.json'));
            if ($service_data) {
                $services[basename($file, '.json')] = $service_data;
            }
        }

        return $services;
    }

    /**
     * Load specific service template
     *
     * @param string $service_slug Service slug (filename without .json)
     * @return array|false Service data or false on failure
     */
    public static function loadServiceTemplate($service_slug) {
        $file_path = RAE_PLUGIN_DIR . 'services/' . $service_slug . '.json';

        if (!file_exists($file_path)) {
            return false;
        }

        $json_content = file_get_contents($file_path);
        $service_data = json_decode($json_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        $service_data['slug'] = $service_slug;
        return $service_data;
    }

    /**
     * Render service field based on type
     *
     * @param array $field Field configuration
     * @param mixed $value Current value
     * @param string $name_prefix Field name prefix
     * @return string HTML output
     */
    public static function renderField($field, $value = '', $name_prefix = 'rae_field') {
        $field_name = $name_prefix . '[' . $field['name'] . ']';
        $field_id = sanitize_key($name_prefix . '_' . $field['name']);
        $required = !empty($field['required']) ? 'required' : '';
        $value = $value !== '' ? $value : ($field['default'] ?? '');

        ob_start();
        ?>
        <tr>
            <th scope="row">
                <label for="<?php echo esc_attr($field_id); ?>">
                    <?php echo esc_html($field['label']); ?>
                    <?php if (!empty($field['required'])): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
            </th>
            <td>
                <?php
                switch ($field['type']) {
                    case 'text':
                        ?>
                        <input 
                            type="text" 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            value="<?php echo esc_attr($value); ?>" 
                            class="regular-text"
                            <?php echo $required; ?>
                            placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                        />
                        <?php
                        break;

                    case 'password':
                        ?>
                        <input 
                            type="password" 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            value="<?php echo esc_attr($value); ?>" 
                            class="regular-text"
                            <?php echo $required; ?>
                            placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                        />
                        <?php
                        break;

                    case 'textarea':
                        ?>
                        <textarea 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>" 
                            rows="4"
                            class="large-text"
                            <?php echo $required; ?>
                            placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                        ><?php echo esc_textarea($value); ?></textarea>
                        <?php
                        break;

                    case 'select':
                        ?>
                        <select 
                            id="<?php echo esc_attr($field_id); ?>" 
                            name="<?php echo esc_attr($field_name); ?>"
                            <?php echo $required; ?>
                        >
                            <?php foreach ($field['options'] as $option_value => $option_label): ?>
                                <option 
                                    value="<?php echo esc_attr($option_value); ?>"
                                    <?php selected($value, $option_value); ?>
                                >
                                    <?php echo esc_html($option_label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                        break;

                    case 'checkbox':
                        ?>
                        <label>
                            <input 
                                type="checkbox" 
                                id="<?php echo esc_attr($field_id); ?>" 
                                name="<?php echo esc_attr($field_name); ?>" 
                                value="1"
                                <?php checked($value, '1'); ?>
                                <?php checked($value, true); ?>
                            />
                            <?php echo esc_html($field['help'] ?? ''); ?>
                        </label>
                        <?php
                        break;
                }
                ?>

                <?php if (!empty($field['help']) && $field['type'] !== 'checkbox'): ?>
                    <p class="description"><?php echo esc_html($field['help']); ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * Build final configuration from template and field values
     *
     * @param string $service_slug Service slug
     * @param array $field_values Field values from form
     * @return array|false Final configuration or false on failure
     */
    public static function buildConfig($service_slug, $field_values) {
        $template = self::loadServiceTemplate($service_slug);
        
        if (!$template) {
            return false;
        }

        $config = $template['config'];

        // Replace placeholders in config with actual values
        $config = self::replacePlaceholders($config, $field_values);

        // Add field values to config for reference
        $config['_field_values'] = $field_values;
        $config['_service_slug'] = $service_slug;

        return $config;
    }

    /**
     * Replace placeholders recursively
     *
     * @param mixed $data Data to process
     * @param array $values Values to replace
     * @return mixed Processed data
     */
    private static function replacePlaceholders($data, $values) {
        if (is_array($data)) {
            $result = [];
            foreach ($data as $key => $value) {
                $result[$key] = self::replacePlaceholders($value, $values);
            }
            return $result;
        }

        if (is_string($data)) {
            foreach ($values as $key => $value) {
                $data = str_replace('{{' . $key . '}}', $value, $data);
            }
        }

        return $data;
    }

    /**
     * Extract field values from saved config
     *
     * @param array $config Saved configuration
     * @return array Field values
     */
    public static function extractFieldValues($config) {
        if (isset($config['_field_values'])) {
            return $config['_field_values'];
        }

        return [];
    }

    /**
     * Get service slug from saved config
     *
     * @param array $config Saved configuration
     * @return string|false Service slug or false
     */
    public static function getServiceSlug($config) {
        return $config['_service_slug'] ?? false;
    }
}

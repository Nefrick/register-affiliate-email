<?php
/**
 * Service Router
 * Routes subscription requests to active services
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

class ServiceRouter {
    /**
     * Get all active services
     *
     * @return array Array of service instances
     */
    public static function getActiveServices() {
        $services = [];

        // Get enabled services from settings
        $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
        $enabled_service_ids = isset($settings['enabled_services']) ? (array) $settings['enabled_services'] : [];

        if (empty($enabled_service_ids)) {
            return $services;
        }

        // Get only enabled services
        $query = new \WP_Query([
            'post_type' => 'rae_service',
            'post_status' => 'any', // Allow any status
            'post__in' => $enabled_service_ids,
            'posts_per_page' => -1,
            'no_found_rows' => true,
        ]);

        if (!$query->have_posts()) {
            return $services;
        }

        foreach ($query->posts as $post) {
            $config_json = get_post_meta($post->ID, '_rae_service_config', true);
            
            if (empty($config_json)) {
                continue;
            }

            $config = json_decode($config_json, true);
            
            if (!is_array($config) || empty($config['service_type'])) {
                continue;
            }

            // Extract field values from config
            $field_values = ServiceTemplateManager::extractFieldValues($config);
            
            // Create service instance
            $service = ServiceFactory::create(
                $config['service_type'],
                $post->ID,
                $field_values
            );

            if (!is_wp_error($service)) {
                $services[] = $service;
            }
        }

        return $services;
    }

    /**
     * Subscribe email to all active services
     *
     * @param string $email Email address
     * @param array $additional_data Additional data to pass to services
     * @return array Results from each service
     */
    public static function subscribeToAll($email, $additional_data = []) {
        $services = self::getActiveServices();
        $results = [
            'success' => [],
            'failed' => [],
            'total' => count($services),
        ];

        if (empty($services)) {
            $results['failed']['general'] = __('No active email services configured.', 'register-affiliate-email');
            return $results;
        }

        foreach ($services as $service) {
            $service_type = $service->getType();
            
            // Validate service
            $validation = $service->validate();
            if (is_wp_error($validation)) {
                $results['failed'][$service_type] = $validation->get_error_message();
                continue;
            }

            // Authenticate
            $auth = $service->authenticate();
            if (is_wp_error($auth)) {
                $results['failed'][$service_type] = $auth->get_error_message();
                continue;
            }

            // Subscribe
            $subscribe_result = $service->subscribe($email, $additional_data);
            
            if (is_wp_error($subscribe_result)) {
                $results['failed'][$service_type] = $subscribe_result->get_error_message();
            } else {
                $results['success'][$service_type] = true;
            }
        }

        return $results;
    }

    /**
     * Subscribe to specific service by ID
     *
     * @param int $service_id Service post ID
     * @param string $email Email address
     * @param array $additional_data Additional data
     * @return bool|\WP_Error
     */
    public static function subscribeToService($service_id, $email, $additional_data = []) {
        $config_json = get_post_meta($service_id, '_rae_service_config', true);
        
        if (empty($config_json)) {
            return new \WP_Error(
                'service_not_found',
                __('Service configuration not found.', 'register-affiliate-email')
            );
        }

        $config = json_decode($config_json, true);
        
        if (!is_array($config) || empty($config['service_type'])) {
            return new \WP_Error(
                'invalid_config',
                __('Invalid service configuration.', 'register-affiliate-email')
            );
        }

        $field_values = ServiceTemplateManager::extractFieldValues($config);
        
        $service = ServiceFactory::create(
            $config['service_type'],
            $service_id,
            $field_values
        );

        if (is_wp_error($service)) {
            return $service;
        }

        // Validate
        $validation = $service->validate();
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Authenticate
        $auth = $service->authenticate();
        if (is_wp_error($auth)) {
            return $auth;
        }

        // Subscribe
        return $service->subscribe($email, $additional_data);
    }
}

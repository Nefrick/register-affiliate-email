<?php
/**
 * Service Factory
 * Creates service instances based on type
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

class ServiceFactory {
    /**
     * Service type to class mapping
     *
     * @var array
     */
    private static $service_classes = [
        'aweber' => AWeberService::class,
        'customerio' => CustomerIOService::class,
        'mailchimp' => MailchimpService::class,
    ];

    /**
     * Create service instance
     *
     * @param string $type Service type
     * @param int $service_id Service post ID
     * @param array $config Service configuration
     * @return AbstractService|\WP_Error Service instance or error
     */
    public static function create($type, $service_id, $config) {
        if (!isset(self::$service_classes[$type])) {
            return new \WP_Error(
                'invalid_service_type',
                sprintf('Unknown service type: %s', $type)
            );
        }

        $class = self::$service_classes[$type];

        if (!class_exists($class)) {
            return new \WP_Error(
                'service_class_not_found',
                sprintf('Service class not found: %s', $class)
            );
        }

        return new $class($service_id, $config);
    }

    /**
     * Register custom service type
     *
     * @param string $type Service type identifier
     * @param string $class_name Service class name (must extend AbstractService)
     * @return bool True on success, false on failure
     */
    public static function registerService($type, $class_name) {
        if (isset(self::$service_classes[$type])) {
            return false;
        }

        if (!class_exists($class_name)) {
            return false;
        }

        self::$service_classes[$type] = $class_name;
        return true;
    }

    /**
     * Get all registered service types
     *
     * @return array
     */
    public static function getRegisteredTypes() {
        return array_keys(self::$service_classes);
    }
}

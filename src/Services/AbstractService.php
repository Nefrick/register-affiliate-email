<?php
/**
 * Abstract Service Class
 * Base class for all email service integrations
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

abstract class AbstractService {
    /**
     * Service configuration
     *
     * @var array
     */
    protected $config = [];

    /**
     * Service post ID
     *
     * @var int
     */
    protected $service_id;

    /**
     * Constructor
     *
     * @param int $service_id Service post ID
     * @param array $config Service configuration
     */
    public function __construct($service_id, $config) {
        $this->service_id = $service_id;
        $this->config = $config;
    }

    /**
     * Get service type identifier
     *
     * @return string
     */
    abstract public function getType();

    /**
     * Validate service configuration
     *
     * @return bool|\WP_Error True if valid, WP_Error if invalid
     */
    abstract public function validate();

    /**
     * Authenticate with the service
     *
     * @return bool|\WP_Error True if authenticated, WP_Error on failure
     */
    abstract public function authenticate();

    /**
     * Subscribe email to the service
     *
     * @param string $email Email address to subscribe
     * @param array $additional_data Additional data (name, custom fields, etc.)
     * @return bool|\WP_Error True on success, WP_Error on failure
     */
    abstract public function subscribe($email, $additional_data = []);

    /**
     * Get configuration value
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    protected function getConfig($key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    /**
     * Make HTTP request to service API
     *
     * @param string $url API endpoint URL
     * @param array $args Request arguments
     * @return array|\WP_Error Response or error
     */
    protected function makeRequest($url, $args = []) {
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Parse JSON response
        $data = json_decode($body, true);

        if ($status_code >= 200 && $status_code < 300) {
            return [
                'success' => true,
                'data' => $data,
                'status_code' => $status_code
            ];
        }

        return new \WP_Error(
            'api_error',
            $data['message'] ?? 'API request failed',
            [
                'status_code' => $status_code,
                'response' => $data
            ]
        );
    }

    /**
     * Replace placeholders in string with actual values
     *
     * @param string $template Template string with {{placeholders}}
     * @param array $values Values to replace
     * @return string
     */
    protected function replacePlaceholders($template, $values) {
        foreach ($values as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    /**
     * Log service activity
     *
     * @param string $message Log message
     * @param string $level Log level (info, warning, error)
     */
    protected function log($message, $level = 'info') {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[RAE Service %s] %s: %s',
                $this->getType(),
                strtoupper($level),
                $message
            ));
        }
    }
}

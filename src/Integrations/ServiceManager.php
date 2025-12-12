<?php
/**
 * Service Integration Manager
 *
 * @package RegisterAffiliateEmail\Integrations
 */

namespace RegisterAffiliateEmail\Integrations;

class ServiceManager {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_rae_submit_email', [$this, 'handleSubmission']);
        add_action('wp_ajax_nopriv_rae_submit_email', [$this, 'handleSubmission']);
    }

    /**
     * Handle form submission
     */
    public function handleSubmission() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rae_submit_nonce')) {
            wp_send_json_error(['message' => __('Security check failed.', 'register-affiliate-email')]);
        }

        // Validate email
        $email = sanitize_email($_POST['email'] ?? '');
        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Invalid email address.', 'register-affiliate-email')]);
        }

        // Get enabled services
        $settings = \RegisterAffiliateEmail\Admin\Settings::getSettings();
        $enabled_services = $settings['enabled_services'] ?? [];

        if (empty($enabled_services)) {
            wp_send_json_error(['message' => __('No services configured.', 'register-affiliate-email')]);
        }

        $results = [];
        $success_count = 0;

        // Submit to each enabled service
        foreach ($enabled_services as $service_id) {
            $result = $this->submitToService($service_id, $email);
            $results[] = $result;
            if ($result['success']) {
                $success_count++;
            }
        }

        if ($success_count > 0) {
            wp_send_json_success([
                'message' => __('Successfully subscribed!', 'register-affiliate-email'),
                'results' => $results
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Subscription failed. Please try again.', 'register-affiliate-email'),
                'results' => $results
            ]);
        }
    }

    /**
     * Submit email to specific service
     *
     * @param int $service_id Service post ID
     * @param string $email Email address
     * @return array Result array with success status and message
     */
    private function submitToService($service_id, $email) {
        $config_json = get_post_meta($service_id, '_rae_service_config', true);
        
        if (empty($config_json)) {
            return [
                'success' => false,
                'service_id' => $service_id,
                'message' => 'No configuration found'
            ];
        }

        $config = json_decode($config_json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'service_id' => $service_id,
                'message' => 'Invalid JSON configuration'
            ];
        }

        // Parse configuration
        $endpoint = $config['endpoint'] ?? '';
        $method = strtoupper($config['method'] ?? 'POST');
        $headers = $config['headers'] ?? [];
        $body_template = $config['body_template'] ?? [];

        if (empty($endpoint)) {
            return [
                'success' => false,
                'service_id' => $service_id,
                'message' => 'No endpoint configured'
            ];
        }

        // Replace placeholders in body template
        $body = $this->replacePlaceholders($body_template, [
            'email' => $email,
            'list_id' => $config['list_id'] ?? '',
            'api_key' => $config['api_key'] ?? ''
        ]);

        // Make HTTP request
        $args = [
            'method' => $method,
            'headers' => $headers,
            'body' => json_encode($body),
            'timeout' => 15
        ];

        $response = wp_remote_request($endpoint, $args);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'service_id' => $service_id,
                'message' => $response->get_error_message()
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        // Consider 2xx status codes as success
        $success = $status_code >= 200 && $status_code < 300;

        return [
            'success' => $success,
            'service_id' => $service_id,
            'status_code' => $status_code,
            'message' => $success ? 'Successfully submitted' : 'Submission failed',
            'response' => $response_body
        ];
    }

    /**
     * Replace placeholders in template
     *
     * @param mixed $template Template array or string
     * @param array $data Data to replace
     * @return mixed
     */
    private function replacePlaceholders($template, $data) {
        if (is_array($template)) {
            $result = [];
            foreach ($template as $key => $value) {
                $result[$key] = $this->replacePlaceholders($value, $data);
            }
            return $result;
        }

        if (is_string($template)) {
            foreach ($data as $key => $value) {
                $template = str_replace('{{' . $key . '}}', $value, $template);
            }
        }

        return $template;
    }
}

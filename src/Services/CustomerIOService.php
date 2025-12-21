<?php
/**
 * Customer.io Service Integration
 *
 * @package RegisterAffiliateEmail\Services
 */

namespace RegisterAffiliateEmail\Services;

class CustomerIOService extends AbstractService {
    /**
     * Get service type
     *
     * @return string
     */
    public function getType() {
        return 'customerio';
    }

    /**
     * Validate configuration
     *
     * @return bool|\WP_Error
     */
    public function validate() {
        $required_fields = ['tracking_site_id', 'tracking_api_key'];

        foreach ($required_fields as $field) {
            if (empty($this->getConfig($field))) {
                return new \WP_Error(
                    'invalid_config',
                    sprintf('Missing required field: %s', $field)
                );
            }
        }

        return true;
    }

    /**
     * Authenticate with Customer.io
     *
     * @return bool|\WP_Error
     */
    public function authenticate() {
        $validation = $this->validate();
        
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Authentication validated
        return true;
    }

    /**
     * Subscribe email to Customer.io
     *
     * @param string $email Email address
     * @param array $additional_data Additional customer data
     * @return bool|\WP_Error
     */
    public function subscribe($email, $additional_data = []) {
       
        // Create base64 credentials for Basic Auth
        $site_id = $this->getConfig('tracking_site_id');
        $api_key = $this->getConfig('tracking_api_key');
     
        $credentials = base64_encode("{$site_id}:{$api_key}");

        // Customer.io customer identifier (use email hash for consistency)
        $customer_id = md5(strtolower(trim($email)));
        $endpoint = "https://track.customer.io/api/v1/customers/{$customer_id}";

        $body = [
            'email' => $email,
            'created_at' => time(),
        ];

        // Merge additional data
        if (!empty($additional_data)) {
            $body = array_merge($body, $additional_data);
        }

        $args = [
            'method' => 'PUT',
            'headers' => [
                'Authorization' => 'Basic ' . $credentials,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($body),
            'timeout' => 30,
        ];

        $response = $this->makeRequest($endpoint, $args);

        if (is_wp_error($response)) {
            $error_data = $response->get_error_data();
            $this->log('Subscription failed: ' . $response->get_error_message() . ' | Data: ' . print_r($error_data, true), 'error');
            return $response;
        }

        // Add to segment if segment_id and api_key are provided
        $segment_id = $this->getConfig('segment_id');
        $app_api_key = $this->getConfig('api_key');
        
        if (!empty($segment_id) && !empty($app_api_key)) {
           
            $segment_result = $this->addToSegment($customer_id, $segment_id, $app_api_key);
            
            if (is_wp_error($segment_result)) {
                $this->log('Failed to add to segment: ' . $segment_result->get_error_message(), 'warning');
                // Don't fail the whole subscription if segment add fails
            } else {
                //$this->log('Added to segment successfully');
            }
        }

        return true;
    }

    /**
     * Add customer to segment using App API
     *
     * @param string $customer_id Customer ID (URL-encoded email)
     * @param string $segment_id Segment ID
     * @param string $api_key App API Key
     * @return bool|\WP_Error
     */
    protected function addToSegment($customer_id, $segment_id, $api_key) {
        // Customer.io API for adding to manual segments (use email as id_type)
        $endpoint = "https://api.customer.io/v1/api/segments/{$segment_id}/add_customers?id_type=email";

        $this->log("Segment endpoint: {$endpoint}");
        $this->log("Email for segment: {$customer_id}");
        $this->log("Segment ID: {$segment_id}");

        $args = [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'ids' => [$customer_id]
            ]),
            'timeout' => 30,
        ];

        $this->log("Segment request body: " . json_encode(['ids' => [$customer_id]]));

        $result = $this->makeRequest($endpoint, $args);
        
        if (is_wp_error($result)) {
            $error_data = $result->get_error_data();
            $this->log('Segment add error: ' . $result->get_error_message() . ' | Status: ' . ($error_data['status_code'] ?? 'unknown') . ' | Response: ' . print_r($error_data['response'] ?? [], true), 'error');
        } else {
            $this->log('Segment API response: ' . print_r($result, true));
        }
        
        return $result;
    }
}

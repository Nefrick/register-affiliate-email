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

        // Get segment_id from post_meta if post_id is provided, and add post_id to profile
        $segment_id = null;
        if (!empty($additional_data['post_id'])) {
            $post_id = (int)$additional_data['post_id'];
            $body['post_id'] = $post_id;
            $segment_meta = get_post_meta($post_id, '_rae_customerio_segment_id', true);
            if (is_array($segment_meta) && !empty($segment_meta['id'])) {
                $segment_id = $segment_meta['id'];
                $body['segment_id'] = $segment_id;
                if (!empty($segment_meta['title'])) {
                    $body['segment_title'] = $segment_meta['title']; // segment name
                }
            } elseif (!empty($segment_meta)) {
                // fallback: если вдруг в мета лежит просто id
                $segment_id = $segment_meta;
                $body['segment_id'] = $segment_id;
            }

            // Add page name and slug if post_id is set
            $body['page_name'] = get_the_title($post_id);
            $body['page_slug'] = get_post_field('post_name', $post_id);
        }

        // Add site name
        $body['site'] = get_bloginfo('name');
        // Add site domain
        $body['site_domain'] = parse_url(home_url(), PHP_URL_HOST);
        // Add current locale
        $body['locale'] = get_locale();

        // Merge additional data (excluding post_id and segment_id to avoid duplication)
        if (!empty($additional_data)) {
            $body = array_merge($body, array_diff_key($additional_data, ['post_id' => true, 'segment_id' => true]));
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
            return $response;
        }

        // Add to segment if segment_id and api_key are provided (segment_id from post_meta)
        $app_api_key = $this->getConfig('api_key');
        if (!empty($segment_id) && !empty($app_api_key)) {
            $segment_result = $this->addToSegment($email, $segment_id, $app_api_key);
            // Don't fail the whole subscription if segment add fails
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

        $result = $this->makeRequest($endpoint, $args);
        return $result;
    }
}

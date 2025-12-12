<?php
/**
 * Subscription REST API Endpoint
 *
 * @package RegisterAffiliateEmail\API
 */

namespace RegisterAffiliateEmail\API;

use RegisterAffiliateEmail\Services\ServiceRouter;

class SubscriptionEndpoint {
    /**
     * API namespace
     *
     * @var string
     */
    private $namespace = 'rae/v1';

    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register REST API routes
     */
    public function registerRoutes() {
        // Subscribe to all active services
        register_rest_route($this->namespace, '/subscribe', [
            'methods' => 'POST',
            'callback' => [$this, 'handleSubscription'],
            'permission_callback' => '__return_true',
            'args' => [
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'validate_callback' => function($param) {
                        return is_email($param);
                    },
                    'sanitize_callback' => 'sanitize_email',
                ],
                'additional_data' => [
                    'required' => false,
                    'type' => 'object',
                    'default' => [],
                ],
            ],
        ]);

        // Subscribe to specific service
        register_rest_route($this->namespace, '/subscribe/(?P<service_id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'handleServiceSubscription'],
            'permission_callback' => '__return_true',
            'args' => [
                'service_id' => [
                    'required' => true,
                    'type' => 'integer',
                    'validate_callback' => function($param) {
                        return is_numeric($param) && $param > 0;
                    },
                ],
                'email' => [
                    'required' => true,
                    'type' => 'string',
                    'validate_callback' => function($param) {
                        return is_email($param);
                    },
                    'sanitize_callback' => 'sanitize_email',
                ],
                'additional_data' => [
                    'required' => false,
                    'type' => 'object',
                    'default' => [],
                ],
            ],
        ]);

        // Get active services info
        register_rest_route($this->namespace, '/services', [
            'methods' => 'GET',
            'callback' => [$this, 'getServicesInfo'],
            'permission_callback' => '__return_true',
        ]);
    }

    /**
     * Handle subscription to all services
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function handleSubscription($request) {
        $email = $request->get_param('email');
        $additional_data = $request->get_param('additional_data');

        // Validate email
        if (!is_email($email)) {
            return new \WP_Error(
                'invalid_email',
                __('Invalid email address.', 'register-affiliate-email'),
                ['status' => 400]
            );
        }

        // Subscribe to all active services
        $results = ServiceRouter::subscribeToAll($email, $additional_data);

        if (is_wp_error($results)) {
            return $results;
        }

        // Check if at least one service succeeded
        $has_success = !empty($results['success']);
        
        $response = [
            'success' => $has_success,
            'message' => $has_success 
                ? __('Thank you for subscribing!', 'register-affiliate-email')
                : __('Subscription failed. Please try again later.', 'register-affiliate-email'),
            'results' => $results,
        ];

        return new \WP_REST_Response($response, 200);
    }

    /**
     * Handle subscription to specific service
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response|\WP_Error
     */
    public function handleServiceSubscription($request) {
        $service_id = $request->get_param('service_id');
        $email = $request->get_param('email');
        $additional_data = $request->get_param('additional_data');

        // Validate email
        if (!is_email($email)) {
            return new \WP_Error(
                'invalid_email',
                __('Invalid email address.', 'register-affiliate-email'),
                ['status' => 400]
            );
        }

        // Subscribe to specific service
        $result = ServiceRouter::subscribeToService($service_id, $email, $additional_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new \WP_REST_Response([
            'success' => true,
            'message' => __('Successfully subscribed to service.', 'register-affiliate-email'),
        ], 200);
    }

    /**
     * Get active services information
     *
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response
     */
    public function getServicesInfo($request) {
        $services = ServiceRouter::getActiveServices();

        $services_info = array_map(function($service) {
            return [
                'type' => $service->getType(),
                'valid' => !is_wp_error($service->validate()),
            ];
        }, $services);

        return new \WP_REST_Response([
            'total' => count($services),
            'services' => $services_info,
        ], 200);
    }
}

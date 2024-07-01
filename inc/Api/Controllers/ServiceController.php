<?php
namespace Inc\Api\Controllers;

use Inc\Api\Services\ServiceService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class ServiceController extends WP_REST_Controller
{
    private $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function register()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    protected function get_namespace()
    {
        return 'appointment_management/v1';
    }

    protected function get_base()
    {
        return 'services';
    }

    public function register_routes()
    {
        $namespace = $this->get_namespace();
        $base = $this->get_base();

        $routes = [
            [
                'route' => '',
                'methods' => 'GET',
                'callback' => 'get_all_services',
                'permission_callback' => '__return_true'
            ],
            [
                'route' => '/create',
                'methods' => 'POST',
                'callback' => 'post_service_data',
                'permission_callback' => [$this, 'can_edit_posts']
            ],
            [
                'route' => '/delete/(?P<id>\d+)',
                'methods' => 'DELETE',
                'callback' => 'delete_service_data',
                'permission_callback' => [$this, 'can_edit_posts']
            ],
            [
                'route' => '/update/(?P<id>\d+)',
                'methods' => 'PUT',
                'callback' => 'update_service_data',
                'permission_callback' => [$this, 'can_edit_posts']
            ]
        ];

        foreach ($routes as $route) {
            register_rest_route($namespace, $base . $route['route'], [
                'methods' => $route['methods'],
                'callback' => [$this, $route['callback']],
                'permission_callback' => $route['permission_callback'],
                'args' => $route['args'] ?? []
            ]);
        }
    }

    public function can_edit_posts()
    {
        return current_user_can('edit_posts');
    }

    private function validate_nonce(WP_REST_Request $request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        return true;
    }

    public function get_all_services(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $services = $this->serviceService->getAllServices();
        return new WP_REST_Response($services, 200);
    }

    public function post_service_data(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_data = $request->get_json_params();
        $result = $this->serviceService->createService($service_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response('Service created successfully', 201);
    }

    public function delete_service_data(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_id = intval($request['id']);
        $result = $this->serviceService->deleteService($service_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response('Service deleted successfully', 200);
    }

    public function update_service_data(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_id = intval($request['id']);
        $service_data = $request->get_json_params();
        $result = $this->serviceService->updateService($service_id, $service_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response('Service updated successfully', 200);
    }
}
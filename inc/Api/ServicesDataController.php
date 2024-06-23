<?php

/**
 * @package AppointmentManagementPlugin
 */

namespace Inc\Api;

/**
 * Custom REST API controller for handling custom data. 
 * Endpoints for getting all services and creating new services.
 */
class ServicesDataController extends RestController
{
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

        // Route definitions
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
            \register_rest_route($namespace, $base . $route['route'], [
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

    private function validate_nonce(\WP_REST_Request $request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        return true;
    }

    public function get_all_services(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        $query = "SELECT id, name, description, FLOOR(TIME_TO_SEC(duration)/60) as duration, price FROM $table_name";
        $services = $wpdb->get_results($query);

        return new \WP_REST_Response($services, 200);
    }

    public function post_service_data(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_data = $request->get_json_params();

        // Validate the service data
        $errors = $this->validate_service_data($service_data);
        if (!empty($errors)) {
            return new \WP_Error('invalid_request', implode(', ', $errors), ['status' => 400]);
        }

        // Insert the services data into the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        // Convert the duration to the right format
        $minutes = $service_data['duration'];
        $hours = floor($minutes / 60);
        $minutes = ($minutes % 60);
        $time = sprintf("%02d:%02d:00", $hours, $minutes);

        $result = $wpdb->insert($table_name, [
            'name' => sanitize_text_field($service_data['name']),
            'description' => sanitize_text_field($service_data['description']),
            'duration' => $time,
            'price' => floatval($service_data['price']),
        ]);

        if ($result === false) {
            return new \WP_Error('db_insert_error', 'Could not insert services into the database', ['status' => 500]);
        }

        return new \WP_REST_Response('Service created successfully', 201);
    }

    private function validate_service_data($data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['price']) || !is_numeric($data['price'])) {
            $errors[] = 'Valid price is required';
        }

        if (empty($data['duration']) || !is_numeric($data['duration']) || $data['duration'] <= 0) {
            $errors[] = 'Valid duration in minutes is required';
        }

        return $errors;
    }

    public function delete_service_data(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_id = intval($request['id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        $result = $wpdb->delete($table_name, ['id' => $service_id]);

        if ($result === false) {
            return new \WP_Error('db_delete_error', 'Could not delete service from the database', ['status' => 500]);
        }

        return new \WP_REST_Response('Service deleted successfully', 200);
    }

    public function update_service_data(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $service_id = intval($request['id']);
        $service_data = $request->get_json_params();

        // Validate the service data
        $errors = $this->validate_service_data($service_data);
        if (!empty($errors)) {
            return new \WP_Error('invalid_request', implode(', ', $errors), ['status' => 400]);
        }

        // Update the services data in the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_services';

        // Convert the duration to the right format
        $minutes = $service_data['duration'];
        $hours = floor($minutes / 60);
        $minutes = ($minutes % 60);
        $time = sprintf("%02d:%02d:00", $hours, $minutes);

        $result = $wpdb->update($table_name, [
            'name' => sanitize_text_field($service_data['name']),
            'description' => sanitize_text_field($service_data['description']),
            'duration' => $time,
            'price' => floatval($service_data['price']),
        ], ['id' => $service_id]);

        if ($result === false) {
            return new \WP_Error('db_update_error', 'Could not update services in the database', ['status' => 500]);
        }

        return new \WP_REST_Response('Service updated successfully', 200);
    }
}

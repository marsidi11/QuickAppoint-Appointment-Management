<?php

namespace Inc\Api\Controllers;

use Inc\Api\Services\AppointmentService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AppointmentController extends WP_REST_Controller 
{
    private $appointmentService;

    public function __construct(AppointmentService $appointmentService) 
    {
        $this->appointmentService = $appointmentService;
    }

    public function register() 
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    protected function get_namespace() 
    {
        return 'appointment_management/v1';
    }

    protected function get_base() 
    {
        return 'appointments';
    }

    public function register_routes() 
    {
        $namespace = $this->get_namespace();
        $base = $this->get_base();

        $routes = [
            [
                'route' => '',
                'methods' => 'GET',
                'callback' => 'get_all_appointments',
                'permission_callback' => [$this, 'can_edit_posts']
            ],
            [
                'route' => '/delete/(?P<id>\d+)',
                'methods' => 'DELETE',
                'callback' => 'delete_appointment_data',
                'permission_callback' => [$this, 'can_edit_posts']
            ],
            [
                'route' => '/create',
                'methods' => 'POST',
                'callback' => 'post_appointment_data',
                'permission_callback' => '__return_true'
            ],
            [
                'route' => '/update/(?P<id>\d+)',
                'methods' => 'PUT',
                'callback' => 'update_appointment_data',
                'permission_callback' => [$this, 'can_edit_posts']
            ],
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

    private function validate_nonce($request)
    {
        $nonce = $request->get_header('X_WP_Nonce');
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error('invalid_nonce', 'Invalid nonce', ['status' => 403]);
        }
        return true;
    }

    public function get_all_appointments(WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }
        
        $page = $request->get_param('page');
        $appointments = $this->appointmentService->getAllAppointments($page);
        
        return new WP_REST_Response($appointments, 200);
    }

    public function post_appointment_data(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $appointment_data = $request->get_json_params();
        $result = $this->appointmentService->createAppointment($appointment_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response('Appointment created successfully. Please check your email for confirmation', 201);
    }

    public function delete_appointment_data(WP_REST_Request $request) 
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $appointment_id = $request['id'];
        $result = $this->appointmentService->deleteAppointment($appointment_id);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response('Appointment deleted successfully', 200);
    }

    public function update_appointment_data(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $appointment_id = $request['id'];
        $appointment_data = $request->get_json_params();
        $result = $this->appointmentService->updateAppointment($appointment_id, $appointment_data);

        if (is_wp_error($result)) {
            return $result;
        }

        return new WP_REST_Response('Appointment updated successfully', 200);
    }
}
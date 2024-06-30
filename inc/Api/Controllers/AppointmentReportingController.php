<?php

namespace Inc\Api\Controllers;

use Inc\Api\RestController;
use Inc\Api\Services\AppointmentReportingService;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AppointmentReportingController extends RestController
{
    private $reportingService;

    public function __construct(AppointmentReportingService $reportingService)
    {
        $this->reportingService = $reportingService;
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
        return 'appointments';
    }

    public function register_routes() 
    {
        $namespace = $this->get_namespace();
        $base = $this->get_base();

        $routes = [
            [
                'route' => '/reserved-time-slots',
                'methods' => 'GET',
                'callback' => 'get_reserved_time_slots',
                'permission_callback' => '__return_true',
                'args' => [
                    'date' => [
                        'required' => true,
                        'validate_callback' => [$this, 'validate_date_format'],
                    ],
                ],
            ],
            [
                'route' => '/search',
                'methods' => 'GET',
                'callback' => 'get_searched_appointments',
                'permission_callback' => [$this, 'can_edit_posts'],
                'args' => [
                    'search' => [
                        'required' => false,
                    ],
                    'dateFilters' => [
                        'required' => false,
                        'type' => 'array',
                    ],
                    'dateRange' => [
                        'required' => false,
                    ],
                    'page' => [
                        'required' => false,
                        'default' => 1,
                    ],
                ],
            ],
        ];

        foreach ($routes as $route) 
        {
            register_rest_route($namespace, '/' . $base . $route['route'], [
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

    public function validate_date_format($param)
    {
        return is_string($param) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $param);
    }
    
    public function get_reserved_time_slots(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $date = $request->get_param('date');
        $reserved_time_slots = $this->reportingService->getReservedTimeSlots($date);

        return new WP_REST_Response($reserved_time_slots, 200);
    }

    public function get_searched_appointments(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $search = $request->get_param('search');
        $date_filters = $request->get_param('dateFilters');
        $date_range = $request->get_param('dateRange');
        $page = $request->get_param('page') ?: 1;

        $appointments = $this->reportingService->searchAppointments($search, $date_filters, $date_range, $page);

        // if (is_wp_error($appointments)) {
        //     return new WP_REST_Response(['error' => $appointments->get_error_message()], 500);
        // }

        return new WP_REST_Response($appointments, 200);
    }
}
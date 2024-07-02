<?php

namespace Inc\Api\Controllers;

use Inc\Api\Services\AppointmentReportingService;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AppointmentReportingController extends WP_REST_Controller
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
                'route' => '/available-time-slots',
                'methods' => 'GET',
                'callback' => 'get_available_time_slots',
                'permission_callback' => '__return_true',
                'args' => [
                    'date' => [
                        'required' => true,
                        'validate_callback' => [$this, 'validate_date_format'],
                    ],
                    'serviceDuration' => [
                        'required' => true,
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
            [
                'route' => '/get-data-report',
                'methods' => 'GET',
                'callback' => 'get_data_report',
                'permission_callback' => '__return_true',
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

    private function validate_nonce($request)
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
    
    public function get_available_time_slots($request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        $date = $request->get_param('date');
        $serviceDuration = $request->get_param('serviceDuration');

        if (!$date || !$serviceDuration) {
            return new WP_Error('missing_params', 'Date and service duration are required', ['status' => 400]);
        }

        $reserved_time_slots = $this->reportingService->getAvailableTimeSlots($date, $serviceDuration);

        return new WP_REST_Response($reserved_time_slots, 200);
    }

    public function get_searched_appointments($request)
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

        return new WP_REST_Response($appointments, 200);
    }

    public function get_data_report(WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        // $startDate = $request->get_param('start_date');
        // $endDate = $request->get_param('end_date');

        $startDate = "2024-06-01";
        $endDate = "2024-06-30";

        if (!$startDate || !$endDate) {
            return new WP_Error('missing_params', 'Start date and end date are required', ['status' => 400]);
        }

        $filepath = $this->reportingService->generateReport($startDate, $endDate);

        if (!file_exists($filepath)) {
            return new WP_Error('report_generation_failed', 'Failed to generate report', ['status' => 500]);
        }

        $file_url = wp_upload_dir()['url'] . '/' . basename($filepath);

        return new WP_REST_Response([
            'message' => 'Report generated successfully',
            'file_url' => $file_url
        ], 200);
    }
}
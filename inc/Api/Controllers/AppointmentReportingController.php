<?php

namespace Inc\Api\Controllers;

use Inc\Api\RestController;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class AppointmentReportingController extends RestController
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
    
    public function get_reserved_time_slots(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }

        // Get the date from the request
        $date = $request->get_param('date');

        // Get the reserved time slots from the database
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $query = $wpdb->prepare("SELECT startTime, endTime FROM $table_name WHERE date = %s", $date);
        $reserved_time_slots = $wpdb->get_results($query);

        return new \WP_REST_Response($reserved_time_slots, 200);
    }

    public function get_searched_appointments(\WP_REST_Request $request)
    {
        $nonce_validation = $this->validate_nonce($request);
        if (is_wp_error($nonce_validation)) {
            return $nonce_validation;
        }
        
        error_log("get_searched_appointments function called");

        global $wpdb;
        $appointments_table = $wpdb->prefix . 'am_appointments';
        $services_table = $wpdb->prefix . 'am_services';
        $mapping_table = $wpdb->prefix . 'am_mapping';

        $search = $request->get_param('search');
        $date_filters = $request->get_param('dateFilters');
        $date_range_filter = $request->get_param('dateRange');
        $page = $request->get_param('page') ?: 1;
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $where_clauses = [];
        $where_params = [];

        // Handle search
        if (!empty($search)) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where_clauses[] = "(a.name LIKE %s OR a.phone LIKE %s OR a.email LIKE %s)";
            $where_params = array_merge($where_params, [$search_term, $search_term, $search_term]);
        }

        // Handle date filters
        if (!empty($date_filters) && !is_array($date_filters)) {
            $date_filters = explode(',', $date_filters);
        }

        if (!empty($date_filters)) {
            $date_filter_clauses = [];
            foreach ($date_filters as $filter) {
                switch ($filter) {
                    case 'today':
                        $date_filter_clauses[] = "a.date = %s";
                        $where_params[] = date('Y-m-d');
                        break;
                    case 'tomorrow':
                        $date_filter_clauses[] = "a.date = %s";
                        $where_params[] = date('Y-m-d', strtotime('+1 day'));
                        break;
                    case 'upcoming':
                        $date_filter_clauses[] = "a.date >= %s";
                        $where_params[] = date('Y-m-d');
                        break;
                    case 'lastMonth':
                        $firstDayLastMonth = date('Y-m-01', strtotime('last month'));
                        $lastDayLastMonth = date('Y-m-t', strtotime('last month'));
                        $date_filter_clauses[] = "a.date BETWEEN %s AND %s";
                        $where_params[] = $firstDayLastMonth;
                        $where_params[] = $lastDayLastMonth;
                        break;
                        // Add more cases for other date filters
                }
            }
            if (!empty($date_filter_clauses)) {
                $where_clauses[] = '(' . implode(' OR ', $date_filter_clauses) . ')';
            }
        }

        // Handle date range filter
        if (!empty($date_range_filter)) {
            switch ($date_range_filter) {
                case 'nextMonth':
                    $start_date = date('Y-m-01', strtotime('+1 month'));
                    $end_date = date('Y-m-t', strtotime('+1 month'));
                    break;
                case 'previousMonth':
                    $start_date = date('Y-m-01', strtotime('-1 month'));
                    $end_date = date('Y-m-t', strtotime('-1 month'));
                    break;
                    // Add more cases for other date ranges
            }
            if (isset($start_date) && isset($end_date)) {
                $where_clauses[] = "a.date BETWEEN %s AND %s";
                $where_params[] = $start_date;
                $where_params[] = $end_date;
            }
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        $query = $wpdb->prepare(
            "SELECT a.*, 
            DATE_FORMAT(a.startTime, '%%H:%%i') as startTime,
            DATE_FORMAT(a.endTime, '%%H:%%i') as endTime,
            GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
            SUM(s.price) as total_price
        FROM $appointments_table a
        LEFT JOIN $mapping_table m ON a.id = m.appointment_id
        LEFT JOIN $services_table s ON m.service_id = s.id
        $where_sql
        GROUP BY a.id
        ORDER BY a.date ASC, a.startTime ASC
        LIMIT %d OFFSET %d",
            array_merge($where_params, [$items_per_page, $offset])
        );

        error_log("Parameters: " . print_r($where_params, true));

        $appointments = $wpdb->get_results($query);

        if ($wpdb->last_error) {
            error_log("Database Error: " . $wpdb->last_error);
            return new \WP_REST_Response(['error' => $wpdb->last_error], 500);
        }

        error_log("Appointments found: " . count($appointments));

        return new \WP_REST_Response($appointments, 200);
    }

}
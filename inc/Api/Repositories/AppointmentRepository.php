<?php
/**
 * Appointment Repository
 *
 * This file defines the AppointmentRepository class, responsible for managing the database interactions
 * related to appointments. It provides methods to create, read, update, and delete appointments from the database.
 * The class uses the WordPress database abstraction layer to perform SQL operations, ensuring compatibility
 * and security. Each method is designed to handle specific data operations for the Appointment entity, making
 * it an integral part of the plugin's data layer.
 */

namespace Inc\Api\Repositories;

use Inc\Api\Models\Appointment;
use WP_Error;
 
class AppointmentRepository
{
    private $wpdb;
    private $appointments_table;
    private $mapping_table;
    private $services_table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->appointments_table = $wpdb->prefix . 'am_appointments';
        $this->mapping_table = $wpdb->prefix . 'am_mapping';
        $this->services_table = $wpdb->prefix . 'am_services';
    }

    public function getAllAppointments($page)
    {
        $items_per_page = 10;
        $offset = ($page - 1) * $items_per_page;

        $current_date = current_time('Y-m-d');
        $current_time = current_time('H:i:s');

        $query = "SELECT a.*, 
                    DATE_FORMAT(a.startTime, '%H:%i') as startTime,
                    DATE_FORMAT(a.endTime, '%H:%i') as endTime,
                    GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
                    SUM(s.price) as total_price
                FROM {$this->appointments_table} a
                LEFT JOIN {$this->mapping_table} m ON a.id = m.appointment_id
                LEFT JOIN {$this->services_table} s ON m.service_id = s.id
                WHERE a.date > '$current_date' OR (a.date = '$current_date' AND a.endTime > '$current_time')
                GROUP BY a.id
                ORDER BY a.date ASC, a.startTime ASC
                LIMIT $items_per_page OFFSET $offset";

        return $this->wpdb->get_results($query);
    }

    public function createAppointment(Appointment $appointment)
    {
        $this->wpdb->query('START TRANSACTION');

        try {
            $result = $this->wpdb->insert($this->appointments_table, [
                'name' => $appointment->getName(),
                'surname' => $appointment->getSurname(),
                'phone' => $appointment->getPhone(),
                'email' => $appointment->getEmail(),
                'date' => $appointment->getDate(),
                'startTime' => $appointment->getStartTime(),
                'endTime' => $appointment->getEndTime(),
                'status' => $appointment->getStatus(),
                'token' => $appointment->getToken()
            ], ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']);

            if ($result === false) {
                throw new \Exception('Could not insert appointment into the database');
            }

            $appointment_id = $this->wpdb->insert_id;

            foreach ($appointment->getServiceIds() as $service_id) {
                $mapping_result = $this->wpdb->insert($this->mapping_table, [
                    'appointment_id' => $appointment_id,
                    'service_id' => $service_id
                ], ['%d', '%d']);

                if ($mapping_result === false) {
                    throw new \Exception('Could not insert appointment-service mapping into the database');
                }
            }

            $this->wpdb->query('COMMIT');

            return $appointment_id;
        } catch (\Exception $e) {
            $this->wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new WP_Error('db_insert_error', $e->getMessage(), ['status' => 500]);
        }
    }

    public function deleteAppointment($appointmentId)
    {
        $this->wpdb->query('START TRANSACTION');

        try {
            $result = $this->wpdb->delete($this->appointments_table, ['id' => $appointmentId]);
            if ($result === false) {
                throw new \Exception('Could not delete appointment from the database');
            }

            $mapping_result = $this->wpdb->delete($this->mapping_table, ['appointment_id' => $appointmentId]);
            if ($mapping_result === false) {
                throw new \Exception('Could not delete appointment-service mapping from the database');
            }

            $this->wpdb->query('COMMIT');

            return true;
        } catch (\Exception $e) {
            $this->wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new WP_Error('db_delete_error', $e->getMessage(), ['status' => 500]);
        }
    }

    // TODO: Implement edit appointment only for some fields
    public function updateAppointment(Appointment $appointment)
    {
        $this->wpdb->query('START TRANSACTION');

        try {
            $result = $this->wpdb->update($this->appointments_table, [
                'name' => $appointment->getName(),
                'surname' => $appointment->getSurname(),
                'phone' => $appointment->getPhone(),
                'email' => $appointment->getEmail(),
                'date' => $appointment->getDate(),
                'startTime' => $appointment->getStartTime(),
                'endTime' => $appointment->getEndTime(),
                'status' => $appointment->getStatus()
            ], ['id' => $appointment->getId()], 
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'], 
            ['%d']);

            if ($result === false) {
                throw new \Exception('Could not update appointment in the database');
            }

            $mapping_result = $this->wpdb->delete($this->mapping_table, ['appointment_id' => $appointment->getId()]);
            if ($mapping_result === false) {
                throw new \Exception('Could not delete appointment-service mapping from the database');
            }

            foreach ($appointment->getServiceIds() as $service_id) {
                $mapping_result = $this->wpdb->insert($this->mapping_table, [
                    'appointment_id' => $appointment->getId(),
                    'service_id' => $service_id
                ], ['%d', '%d']);

                if ($mapping_result === false) {
                    throw new \Exception('Could not insert appointment-service mapping into the database');
                }
            }

            $this->wpdb->query('COMMIT');

            return true;
        } catch (\Exception $e) {
            $this->wpdb->query('ROLLBACK');
            error_log($e->getMessage());
            return new WP_Error('db_update_error', $e->getMessage(), ['status' => 500]);
        }
    }

    public function getReservedTimeSlots(string $date): array
    {
        $query = $this->wpdb->prepare(
            "SELECT startTime, endTime FROM {$this->appointments_table} WHERE date = %s",
            $date
        );
        return $this->wpdb->get_results($query);
    }

    public function filterAppointments(?string $search, ?array $dateFilters, ?string $dateRange, ?array $statusFilters, int $itemsPerPage, int $offset): array
    {
        $where_clauses = [];
        $where_params = [];

        if (!empty($search)) 
        {
            $search_term = '%' . $this->wpdb->esc_like($search) . '%';
            $where_clauses[] = "(a.name LIKE %s OR a.phone LIKE %s OR a.email LIKE %s)";
            $where_params = array_merge($where_params, [$search_term, $search_term, $search_term]);
        }

        if (!empty($dateFilters)) 
        {
            $date_filter_clauses = $this->buildDateFilterClauses($dateFilters, $where_params);
            if (!empty($date_filter_clauses)) {
                $where_clauses[] = '(' . implode(' OR ', $date_filter_clauses) . ')';
            }
        }

        if (!empty($dateRange)) {
            $date_range_clause = $this->buildDateRangeClause($dateRange, $where_params);
            if ($date_range_clause) {
                $where_clauses[] = $date_range_clause;
            }
        }

        if (!empty($statusFilters)) 
        {
            $status_filter_clauses = $this->buildStatusFilterClauses($statusFilters, $where_params);
            if (!empty($status_filter_clauses)) {
                $where_clauses[] = '(' . implode(' OR ', $status_filter_clauses) . ')';
            }
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        $query = $this->wpdb->prepare(
            "SELECT a.*, 
            DATE_FORMAT(a.startTime, '%%H:%%i') as startTime,
            DATE_FORMAT(a.endTime, '%%H:%%i') as endTime,
            GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
            SUM(s.price) as total_price
            FROM {$this->appointments_table} a
            LEFT JOIN {$this->mapping_table} m ON a.id = m.appointment_id
            LEFT JOIN {$this->services_table} s ON m.service_id = s.id
            $where_sql
            GROUP BY a.id
            ORDER BY a.date ASC, a.startTime ASC
            LIMIT %d OFFSET %d",
            array_merge($where_params, [$itemsPerPage, $offset])
        );

        return $this->wpdb->get_results($query);
    }
    
    private function buildDateFilterClauses(array $dateFilters, array &$where_params): array
    {
        $date_filter_clauses = [];
        foreach ($dateFilters as $filter) {
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
                    $last30DaysStart = date('Y-m-d', strtotime('-30 days'));
                    $last30DaysEnd = date('Y-m-d');
                    $date_filter_clauses[] = "a.date BETWEEN %s AND %s";
                    $where_params[] = $last30DaysStart;
                    $where_params[] = $last30DaysEnd;
                    break;
            }
        }
        return $date_filter_clauses;
    }

    private function buildDateRangeClause(string $dateRange, array &$where_params): ?string
    {
        switch ($dateRange) 
        {
            case 'nextMonth':
                $start_date = date('Y-m-01', strtotime('+1 month'));
                $end_date = date('Y-m-t', strtotime('+1 month'));
                break;
            case 'previousMonth':
                $start_date = date('Y-m-01', strtotime('-1 month'));
                $end_date = date('Y-m-t', strtotime('-1 month'));
                break;
            default:
                return null;
        }
        $where_params[] = $start_date;
        $where_params[] = $end_date;
        return "a.date BETWEEN %s AND %s";
    }

    // TODO: Add filter method to show only confirmed, cancelled, or pending appointments
    private function buildStatusFilterClauses(array $statusFilters, array &$where_params): array
    {
        $status_filter_clauses = [];
        foreach ($statusFilters as $status) {
            switch ($status) {
                case 'confirmed':
                    $status_filter_clauses[] = "a.status = %s";
                    $where_params[] = 'Confirmed';
                    break;
                case 'cancelled':
                    $status_filter_clauses[] = "a.status = %s";
                    $where_params[] = 'Cancelled';
                    break;
                case 'pending':
                    $status_filter_clauses[] = "a.status = %s";
                    $where_params[] = 'Pending';
                    break;
            }
        }

        return $status_filter_clauses;
    }

    public function getAppointmentsForReport($startDate, $endDate)
    {
        $query = $this->wpdb->prepare(
            "SELECT a.*, 
            DATE_FORMAT(a.startTime, '%%H:%%i') as startTime,
            DATE_FORMAT(a.endTime, '%%H:%%i') as endTime,
            GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
            GROUP_CONCAT(s.price SEPARATOR ', ') as service_prices,
            SUM(s.price) as total_price
            FROM {$this->appointments_table} a
            LEFT JOIN {$this->mapping_table} m ON a.id = m.appointment_id
            LEFT JOIN {$this->services_table} s ON m.service_id = s.id
            WHERE a.date BETWEEN %s AND %s
            GROUP BY a.id
            ORDER BY a.date ASC, a.startTime ASC",
            $startDate,
            $endDate
        );

        return $this->wpdb->get_results($query);
    }

    public function getAppointmentByToken($token)
    {
        $query = $this->wpdb->prepare(
            "SELECT a.*, 
        DATE_FORMAT(a.startTime, '%%H:%%i') as startTime,
        DATE_FORMAT(a.endTime, '%%H:%%i') as endTime,
        GROUP_CONCAT(s.id SEPARATOR ',') as service_id,
        GROUP_CONCAT(s.name SEPARATOR ', ') as service_names,
        COALESCE(SUM(s.price), 0) as total_price
        FROM {$this->appointments_table} a
        LEFT JOIN {$this->mapping_table} m ON a.id = m.appointment_id
        LEFT JOIN {$this->services_table} s ON m.service_id = s.id
        WHERE a.token = %s
        GROUP BY a.id",
            $token
        );

        $result = $this->wpdb->get_row($query, ARRAY_A);

        if ($result) 
        {
            $result['service_id'] = explode(',', $result['service_id']);
            return new Appointment($result);
        } else 
        {
            return new WP_Error('appointment_not_found', 'Appointment not found');
        }
    }

    public function updateAppointmentStatus($token, $status)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'am_appointments';
        $result = $wpdb->update($table_name, ['status' => $status], ['token' => $token]);

        if ($result === false) 
        {
            return new WP_Error('update_failed', 'Failed to update appointment status');
        }

        return true;
    }
}
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
}
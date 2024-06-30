<?php
/**
 * Mapping Repository.
 *
 * Handles the database operations related to mappings between appointments and services.
 * Provides methods to create, retrieve, check, and delete mappings in the database.
 */

namespace Inc\Api\Repositories;

use WP_Error;

class MappingRepository
{
    private $wpdb;
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'am_mapping';
    }

    /**
     * Create a new mapping between an appointment and a service.
     *
     * @param int $appointmentId The appointment ID.
     * @param int $serviceId The service ID.
     * @return int|WP_Error The inserted row ID or WP_Error on failure.
     */
    public function create($appointmentId, $serviceId)
    {
        $result = $this->wpdb->insert(
            $this->table_name,
            [
                'appointment_id' => $appointmentId,
                'service_id' => $serviceId
            ],
            ['%d', '%d']
        );

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Could not insert mapping into the database.', ['status' => 500]);
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Get all service IDs associated with a specific appointment.
     *
     * @param int $appointmentId The appointment ID.
     * @return array|WP_Error An array of service IDs or WP_Error on failure.
     */
    public function getServiceIdsByAppointmentId($appointmentId)
    {
        $query = $this->wpdb->prepare(
            "SELECT service_id FROM {$this->table_name} WHERE appointment_id = %d",
            $appointmentId
        );

        $results = $this->wpdb->get_col($query);

        if ($results === null) {
            return new WP_Error('db_query_error', 'Could not retrieve service IDs from the database.', ['status' => 500]);
        }

        return array_map('intval', $results);
    }

    /**
     * Get all appointment IDs associated with a specific service.
     *
     * @param int $serviceId The service ID.
     * @return array|WP_Error An array of appointment IDs or WP_Error on failure.
     */
    public function getAppointmentIdsByServiceId($serviceId)
    {
        $query = $this->wpdb->prepare(
            "SELECT appointment_id FROM {$this->table_name} WHERE service_id = %d",
            $serviceId
        );

        $results = $this->wpdb->get_col($query);

        if ($results === null) {
            return new WP_Error('db_query_error', 'Could not retrieve appointment IDs from the database.', ['status' => 500]);
        }

        return array_map('intval', $results);
    }

    /**
     * Delete all mappings for a specific appointment.
     *
     * @param int $appointmentId The appointment ID.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function deleteByAppointmentId($appointmentId)
    {
        $result = $this->wpdb->delete(
            $this->table_name,
            ['appointment_id' => $appointmentId],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error('db_delete_error', 'Could not delete mappings from the database.', ['status' => 500]);
        }

        return true;
    }

    /**
     * Delete all mappings for a specific service.
     *
     * @param int $serviceId The service ID.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function deleteByServiceId($serviceId)
    {
        $result = $this->wpdb->delete(
            $this->table_name,
            ['service_id' => $serviceId],
            ['%d']
        );

        if ($result === false) {
            return new WP_Error('db_delete_error', 'Could not delete mappings from the database.', ['status' => 500]);
        }

        return true;
    }

    /**
     * Check if a mapping exists between an appointment and a service.
     *
     * @param int $appointmentId The appointment ID.
     * @param int $serviceId The service ID.
     * @return bool|WP_Error True if mapping exists, false if it doesn't, WP_Error on failure.
     */
    public function mappingExists($appointmentId, $serviceId)
    {
        $query = $this->wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_name} WHERE appointment_id = %d AND service_id = %d",
            $appointmentId,
            $serviceId
        );

        $count = $this->wpdb->get_var($query);

        if ($count === null) {
            return new WP_Error('db_query_error', 'Could not check if mapping exists in the database.', ['status' => 500]);
        }

        return $count > 0;
    }
}
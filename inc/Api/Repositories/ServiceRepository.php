<?php

/**
 * Service Repository
 *
 * This file defines the ServiceRepository class, responsible for managing the database interactions
 * related to services. It provides methods to create, read, update, and delete services from the database.
 * The class uses the WordPress database abstraction layer to perform SQL operations, ensuring compatibility
 * and security. Each method is designed to handle specific data operations for the Service entity, making
 * it an integral part of the plugin's data layer.
 */

namespace Inc\Api\Repositories;

use Inc\Api\Models\Service;
use WP_Error;

class ServiceRepository
{
    private $wpdb;
    private $services_table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->services_table = $wpdb->prefix . 'am_services';
    }

    /**
     * Retrieve all services.
     *
     * @return array|WP_Error List of services or WP_Error on failure.
     */
    public function getAll()
    {
        $query = "SELECT id, name, description, FLOOR(TIME_TO_SEC(duration)/60) as duration, price FROM {$this->services_table}";
        $results = $this->wpdb->get_results($query, ARRAY_A);

        if ($results === false) {
            return new WP_Error('database_error', 'Failed to retrieve services.');
        }

        // Convert results to Service objects
        return array_map(function ($data) {
            return new Service($data);
        }, $results);
    }


    /**
     * Create a new service.
     *
     * @param Service $serviceData The service data.
     * @return int|WP_Error The created service ID or WP_Error on failure.
     */
    public function create(Service $service)
    {
        $time = $this->convertDurationToTime($service->getDuration());

        $result = $this->wpdb->insert($this->services_table, [
            'name' => sanitize_text_field($service->getName()),
            'description' => sanitize_text_field($service->getDescription()),
            'duration' => $time,
            'price' => floatval($service->getPrice()),
        ]);

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Could not insert service into the database', ['status' => 500]);
        }

        return $this->wpdb->insert_id;
    }

    /**
     * Delete a service.
     *
     * @param int $serviceId The service ID.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function delete($serviceId)
    {
        $result = $this->wpdb->delete($this->services_table, ['id' => $serviceId]);

        if ($result === false) {
            return new WP_Error('db_delete_error', 'Could not delete service from the database', ['status' => 500]);
        }

        return true;
    }

    /**
     * Update a service.
     *
     * @param int $serviceId The service ID.
     * @param array $serviceData The service data.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function updateService(int $serviceId, Service $service)
    {
        $time = $this->convertDurationToTime($service->getDuration());
    
        $result = $this->wpdb->update($this->services_table, [
            'name' => sanitize_text_field($service->getName()),
            'description' => sanitize_text_field($service->getDescription()),
            'duration' => $time,
            'price' => floatval($service->getPrice()),
        ], ['id' => $serviceId]);
    
        if ($result === false) {
            return new WP_Error('db_update_error', 'Could not update service in the database', ['status' => 500]);
        }
    
        return true;
    }

    /**
     * Retrieve a service by its ID.
     *
     * @param int $id The service ID.
     * @return Service|WP_Error Service object on success, WP_Error on failure.
     */
    public function getById($id)
    {
        $query = $this->wpdb->prepare("SELECT * FROM {$this->services_table} WHERE id = %d", $id);
        $result = $this->wpdb->get_row($query);

        if (!$result) {
            return new WP_Error('not_found', 'Service not found.');
        }

        return new Service((array)$result);
    }

    /**
     * Convert duration from minutes to time format.
     *
     * @param int $minutes The duration in minutes.
     * @return string The duration in "HH:MM:SS" format.
     */
    private function convertDurationToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = ($minutes % 60);
        return sprintf("%02d:%02d:00", $hours, $minutes);
    }
}

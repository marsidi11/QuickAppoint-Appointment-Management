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
    private $table_name;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'am_services';
    }

    public function getAll()
    {
        $query = "SELECT id, name, description, FLOOR(TIME_TO_SEC(duration)/60) as duration, price FROM {$this->table_name}";
        $results = $this->wpdb->get_results($query, ARRAY_A);
        
        return array_map(function($data) {
            return new Service($data);
        }, $results);
    }

    public function create($serviceData)
    {
        $time = $this->convertDurationToTime($serviceData['duration']);

        $result = $this->wpdb->insert($this->table_name, [
            'name' => sanitize_text_field($serviceData['name']),
            'description' => sanitize_text_field($serviceData['description']),
            'duration' => $time,
            'price' => floatval($serviceData['price']),
        ]);

        if ($result === false) {
            return new WP_Error('db_insert_error', 'Could not insert service into the database', ['status' => 500]);
        }

        return $this->wpdb->insert_id;
    }

    public function delete($serviceId)
    {
        $result = $this->wpdb->delete($this->table_name, ['id' => $serviceId]);

        if ($result === false) {
            return new WP_Error('db_delete_error', 'Could not delete service from the database', ['status' => 500]);
        }

        return true;
    }

    public function update($serviceId, $serviceData)
    {
        $time = $this->convertDurationToTime($serviceData['duration']);

        $result = $this->wpdb->update($this->table_name, [
            'name' => sanitize_text_field($serviceData['name']),
            'description' => sanitize_text_field($serviceData['description']),
            'duration' => $time,
            'price' => floatval($serviceData['price']),
        ], ['id' => $serviceId]);

        if ($result === false) {
            return new WP_Error('db_update_error', 'Could not update service in the database', ['status' => 500]);
        }

        return true;
    }

    private function convertDurationToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $minutes = ($minutes % 60);
        return sprintf("%02d:%02d:00", $hours, $minutes);
    }
}
<?php
/**
 * Mapping Model
 *
 * Represents the relationship between appointments and services in the system.
 * This model facilitates the linking of a single appointment to one or more services,
 * allowing for a many-to-many relationship between appointments and services.
 * Each instance of this class represents a single record in the mapping table,
 * containing the IDs of both the appointment and the service involved in the mapping.
 */

namespace Inc\Api\Models;

class Mapping
{
    public $id;
    public $appointment_id;
    public $service_id;

    public function __construct($data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->appointment_id = $data['appointment_id'] ?? null;
        $this->service_id = $data['service_id'] ?? null;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'service_id' => $this->service_id,
        ];
    }
}
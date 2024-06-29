<?php
/**
 * Service Model
 *
 * This file contains the Service class which is used to represent a service entity.
 * It includes properties for the service ID, name, description, duration, and price,
 * along with a constructor to initialize these properties and a method to convert
 * the service object to an array.
 */

namespace Inc\Api\Models;

class Service
{
    public $id;
    public $name;
    public $description;
    public $duration;
    public $price;

    public function __construct($data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->duration = $data['duration'] ?? 0;
        $this->price = $data['price'] ?? 0.0;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration' => $this->duration,
            'price' => $this->price,
        ];
    }
}
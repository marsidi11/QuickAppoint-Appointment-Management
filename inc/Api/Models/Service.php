<?php
/**
 * Service Model
 *
 * This file contains the Service class which is used to represent a service entity.
 * It includes properties for the service ID, name, description, duration, and price,
 * along with a constructor to initialize these properties and methods to get and set
 * these properties and to convert the service object to an array.
 */

namespace Inc\Api\Models;

class Service
{
    private $id;
    private $name;
    private $description;
    private $duration;
    private $price;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = sanitize_text_field($data['name'] ?? '');
        $this->description = sanitize_textarea_field($data['description'] ?? '');
        $this->duration = intval($data['duration'] ?? 0);
        $this->price = floatval($data['price'] ?? 0.0);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = sanitize_text_field($name);
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = sanitize_textarea_field($description);
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = intval($duration);
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function setPrice($price)
    {
        $this->price = floatval($price);
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

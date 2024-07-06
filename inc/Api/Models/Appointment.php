<?php

namespace Inc\Api\Models;

class Appointment
{
    private $id;
    private $name;
    private $surname;
    private $phone;
    private $email;
    private $date;
    private $startTime;
    private $endTime;
    private $status;
    private $token;
    private $serviceIds = [];
    private $serviceNames = [];
    private $totalPrice = 0.0;


    public function __construct(array $data)
    {
        $this->name = sanitize_text_field($data['name']);
        $this->surname = sanitize_text_field($data['surname']);
        $this->phone = sanitize_text_field($data['phone']);
        $this->email = sanitize_email($data['email']);
        $this->date = $data['date'];
        $this->startTime = $data['startTime'];
        $this->endTime = $data['endTime'];
        $this->status = $data['status'] ?? 'Pending';
        $this->token = $data['token'] ?? '';
        $this->serviceIds = isset($data['service_id']) ? (is_array($data['service_id']) ? $data['service_id'] : explode(',', $data['service_id'])) : [];
        $this->serviceNames = isset($data['service_names']) ? explode(', ', $data['service_names']) : [];
        $this->totalPrice = floatval($data['total_price'] ?? 0.0);
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

    public function getSurname()
    {
        return $this->surname;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getServiceIds()
    {
        return $this->serviceIds;
    }

    public function getServiceNames()
    {
        return $this->serviceNames;
    }

    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'phone' => $this->phone,
            'email' => $this->email,
            'date' => $this->date,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'status' => $this->status,
            'token' => $this->token,
            'service_id' => $this->serviceIds,
            'service_names' => $this->serviceNames,
            'total_price' => $this->totalPrice,
        ];
    }
}
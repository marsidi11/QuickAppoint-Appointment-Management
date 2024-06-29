<?php
/**
 * Service Management
 *
 * This file contains the ServiceService class, which is responsible for handling
 * the business logic associated with service operations. It interacts with the
 * ServiceRepository to perform CRUD operations on services. This includes fetching
 * all services, creating a new service, deleting an existing service, and updating
 * a service. Additionally, it contains validation logic to ensure that service data
 * is valid before performing operations.
 */

namespace Inc\Api\Services;

use Inc\Api\Repositories\ServiceRepository;
use WP_Error;

class ServiceService
{
    private $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function getAllServices()
    {
        return $this->serviceRepository->getAll();
    }

    public function createService($serviceData)
    {
        $errors = $this->validate_service_data($serviceData);
        if (!empty($errors)) {
            return new WP_Error('invalid_request', implode(', ', $errors), ['status' => 400]);
        }

        return $this->serviceRepository->create($serviceData);
    }

    public function deleteService($serviceId)
    {
        return $this->serviceRepository->delete($serviceId);
    }

    public function updateService($serviceId, $serviceData)
    {
        $errors = $this->validate_service_data($serviceData);
        if (!empty($errors)) {
            return new WP_Error('invalid_request', implode(', ', $errors), ['status' => 400]);
        }

        return $this->serviceRepository->update($serviceId, $serviceData);
    }

    private function validate_service_data($data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['price']) || !is_numeric($data['price'])) {
            $errors[] = 'Valid price is required';
        }

        if (empty($data['duration']) || !is_numeric($data['duration']) || $data['duration'] <= 0) {
            $errors[] = 'Valid duration in minutes is required';
        }

        return $errors;
    }
}
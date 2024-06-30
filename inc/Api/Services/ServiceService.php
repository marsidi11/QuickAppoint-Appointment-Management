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

    /**
     * Retrieve all services.
     *
     * @return array|WP_Error List of services or WP_Error on failure.
     */
    public function getAllServices()
    {
        $services = $this->serviceRepository->getAll();
        if (is_wp_error($services)) {
            return $services;
        }
        return $services;
    }

    /**
     * Create a new service.
     *
     * @param array $serviceData The service data.
     * @return int|WP_Error The created service ID or WP_Error on failure.
     */
    public function createService(array $serviceData)
    {
        $errors = $this->validateServiceData($serviceData);
        if (!empty($errors)) {
            return new WP_Error('invalid_request', implode(', ', $errors), ['status' => 400]);
        }

        $createdService = $this->serviceRepository->create($serviceData);
        if (is_wp_error($createdService)) {
            return $createdService;
        }
        return $createdService;
    }

    /**
     * Delete a service.
     *
     * @param int $serviceId The service ID.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function deleteService(int $serviceId)
    {
        $deleted = $this->serviceRepository->delete($serviceId);
        if (is_wp_error($deleted)) {
            return $deleted;
        }
        return $deleted;
    }

    /**
     * Update a service.
     *
     * @param int $serviceId The service ID.
     * @param array $serviceData The service data.
     * @return bool|WP_Error True on success, WP_Error on failure.
     */
    public function updateService(int $serviceId, array $serviceData)
    {
        $errors = $this->validateServiceData($serviceData);
        if (!empty($errors)) {
            return new WP_Error('invalid_request', implode(', ', $errors), ['status' => 400]);
        }

        $updatedService = $this->serviceRepository->update($serviceId, $serviceData);
        if (is_wp_error($updatedService)) {
            return $updatedService;
        }
        return $updatedService;
    }

    /**
     * Validate service data.
     *
     * @param array $data The service data.
     * @return array List of validation errors.
     */
    private function validateServiceData(array $data)
    {
        $errors = [];

        // Validate name
        if (empty($data['name'])) {
            $errors[] = 'Name is required.';
        } elseif (!is_string($data['name']) || strlen(trim($data['name'])) < 3) {
            $errors[] = 'Name must be a string with at least 3 characters.';
        }

        // Validate price
        if (empty($data['price'])) {
            $errors[] = 'Price is required.';
        } elseif (!is_numeric($data['price']) || $data['price'] <= 0) {
            $errors[] = 'Price must be a positive number.';
        }

        // Validate duration
        if (empty($data['duration'])) {
            $errors[] = 'Duration is required.';
        } elseif (!is_numeric($data['duration']) || $data['duration'] <= 0) {
            $errors[] = 'Duration must be a positive number of minutes.';
        }

        // Validate description
        if (isset($data['description']) && (!is_string($data['description']) || strlen($data['description']) > 500)) {
            $errors[] = 'Description must be a string with a maximum length of 500 characters.';
        }

        return $errors;
    }
}

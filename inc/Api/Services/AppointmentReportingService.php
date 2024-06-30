<?php

namespace Inc\Api\Services;

use Inc\Api\Repositories\AppointmentRepository;
use WP_Error;

class AppointmentReportingService
{
    private $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function getReservedTimeSlots(string $date): array
    {
        return $this->appointmentRepository->getReservedTimeSlots($date);
    }

    public function searchAppointments(?string $search, ?array $dateFilters, ?string $dateRange, int $page): array
    {
        $itemsPerPage = 10;
        $offset = ($page - 1) * $itemsPerPage;

        return $this->appointmentRepository->searchAppointments($search, $dateFilters, $dateRange, $itemsPerPage, $offset);
    }
}
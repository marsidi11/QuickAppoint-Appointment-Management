<?php

namespace Inc\Api\Callbacks;

use DateTime;
use DateInterval;
use Exception;
use InvalidArgumentException;

class TimeSlotGenerator
{
    private $wpdb;
    private $appointments_table;
    private $cache;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->appointments_table = $wpdb->prefix . 'am_appointments';
        $this->cache = [];
    }

    /**
     * Generate available time slots based on given parameters.
     *
     * @param array $params {
     *     @type string $date            The date for which to generate slots (Y-m-d format).
     *     @type int    $serviceDuration Duration of the service in minutes.
     * }
     * @return array An array of available time slots.
     * @throws InvalidArgumentException If input parameters are invalid.
     */
    public function generateAvailableTimeSlots(array $params): array
    {
        $this->validateParams($params);

        $date = $params['date'];
        $serviceDuration = $params['serviceDuration'];

        $businessHours = $this->getBusinessHours($date);
        $breakTimes = $this->getBreakTimes($date);
        $reservedSlots = $this->getReservedTimeSlots($date);
        $minSlotDuration = $this->getSlotDuration();

        $openTime = $this->timeToMinutes($businessHours['openTime']);
        $closeTime = $this->timeToMinutes($businessHours['closeTime']);

        $availableSlots = [];
        $currentTime = $openTime;

        while ($currentTime < $closeTime) {
            if ($this->isWithinBreakTime($currentTime, $breakTimes)) {
                $currentTime = $this->getNextTimeAfterBreak($currentTime, $breakTimes);
                continue;
            }

            $slotEnd = $currentTime + $serviceDuration;

            if ($slotEnd > $closeTime) {
                break;
            }

            if (!$this->isTimeSlotReserved($currentTime, $slotEnd, $reservedSlots)) {
                $availableSlots[] = [
                    'start' => $this->minutesToTime($currentTime),
                    'end' => $this->minutesToTime($slotEnd)
                ];
            }

            $currentTime += $minSlotDuration;
        }

        return $availableSlots;
    }

    private function validateParams(array $params): void
    {
        if (!isset($params['date']) || !$this->isValidDate($params['date'])) {
            throw new InvalidArgumentException("Invalid or missing date parameter");
        }

        if (!isset($params['serviceDuration']) || !is_int($params['serviceDuration']) || $params['serviceDuration'] <= 0) {
            throw new InvalidArgumentException("Invalid or missing serviceDuration parameter");
        }
    }

    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isWithinBreakTime(int $time, array $breakTimes): bool
    {
        foreach ($breakTimes as $breakTime) {
            if ($time >= $breakTime['start'] && $time < $breakTime['end']) {
                return true;
            }
        }
        return false;
    }

    private function getNextTimeAfterBreak(int $time, array $breakTimes): int
    {
        foreach ($breakTimes as $breakTime) {
            if ($time >= $breakTime['start'] && $time < $breakTime['end']) {
                return $breakTime['end'];
            }
        }
        return $time;
    }

    private function isTimeSlotReserved(int $start, int $end, array $reservedSlots): bool
    {
        foreach ($reservedSlots as $slot) {
            if (max($start, $slot['start']) < min($end, $slot['end'])) {
                return true;
            }
        }
        return false;
    }

    private function getBusinessHours(string $date): array
    {
        // Implement day-specific business hours if needed
        return [
            'openTime' => get_option('open_time', '09:00'),
            'closeTime' => get_option('close_time', '17:00')
        ];
    }

    private function getBreakTimes(string $date): array
    {
        // Implement day-specific break times if needed
        $breakStart = get_option('break_start');
        $breakEnd = get_option('break_end');

        if ($breakStart && $breakEnd) {
            return [
                [
                    'start' => $this->timeToMinutes($breakStart),
                    'end' => $this->timeToMinutes($breakEnd)
                ]
            ];
        }

        return [];
    }

    private function getSlotDuration(): int
    {
        return get_option('time_slot_duration', 15); 
    }

    public function getReservedTimeSlots(string $date): array
    {
        if (!isset($this->cache[$date])) {
            $query = $this->wpdb->prepare(
                "SELECT startTime, endTime FROM {$this->appointments_table} WHERE date = %s ORDER BY startTime",
                $date
            );
            $results = $this->wpdb->get_results($query);

            $this->cache[$date] = array_map(function($slot) {
                return [
                    'start' => $this->timeToMinutes($slot->startTime),
                    'end' => $this->timeToMinutes($slot->endTime)
                ];
            }, $results);
        }

        return $this->cache[$date];
    }

    private function timeToMinutes(string $time): int
    {
        list($hours, $minutes) = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }

    private function minutesToTime(int $minutes): string
    {
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
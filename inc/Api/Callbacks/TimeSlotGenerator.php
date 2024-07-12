<?php

namespace Inc\Api\Callbacks;

use DateTime;
use Exception;
use InvalidArgumentException;

class TimeSlotGenerator
{
    private $wpdb;
    private $appointments_table;
    private $cache;
    private $bufferTime;
    private $slotDuration;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->appointments_table = $wpdb->prefix . 'quickappoint_appointments';
        $this->cache = [
            'reserved_slots' => [],
            'business_hours' => [],
            'break_times' => [],
        ];
        $this->bufferTime = $this->getBufferTime();
        $this->slotDuration = $this->getSlotDuration();
    }

    public function generateOptimizedTimeSlots(array $params): array
    {
        try {
            $this->validateParams($params);

            $date = $params['date'];
            $serviceDuration = $params['serviceDuration'];

            $businessHours = $this->getBusinessHours($date);
            $breakTimes = $this->getBreakTimes($date);
            $reservedSlots = $this->getReservedTimeSlots($date);

            $openTime = $this->timeToMinutes($businessHours['openTime']);
            $closeTime = $this->timeToMinutes($businessHours['closeTime']);

            $availableSlots = [];
            $currentTime = $openTime;

            while ($currentTime + $serviceDuration <= $closeTime) {
                $nextSlotStart = $this->roundToNearestSlot($currentTime);
                $slotEnd = $nextSlotStart + $this->slotDuration;

                // Check for regular slots
                if (
                    $this->isTimeSlotAvailable($nextSlotStart, $nextSlotStart + $serviceDuration, $reservedSlots, $breakTimes) &&
                    !$this->isNewAppointmentOverlapping($nextSlotStart, $serviceDuration, $reservedSlots)
                ) {
                    $availableSlots[] = [
                        'start' => $this->minutesToTime($nextSlotStart),
                        'end' => $this->minutesToTime($nextSlotStart + $serviceDuration)
                    ];
                }

                // Check for optimized slots
                $this->addOptimizedSlots($currentTime, $slotEnd, $serviceDuration, $reservedSlots, $breakTimes, $availableSlots);

                $currentTime = $slotEnd;
            }

            return $availableSlots;
        } catch (Exception $e) {
            error_log("Error generating optimized time slots: " . $e->getMessage());
            return [];
        }
    }

    private function addOptimizedSlots(int $start, int $endTime, int $serviceDuration, array $reservedSlots, array $breakTimes, array &$availableSlots): void
    {
        foreach ($reservedSlots as $slot) {
            if ($slot['end'] > $start && $slot['end'] <= $endTime) {
                $gapStart = $this->roundUpToNearest5Minutes($slot['end']);
                $gapEnd = $endTime;


                while ($gapStart + $serviceDuration + $this->bufferTime <= $gapEnd) {
                    $potentialSlotEnd = $gapStart + $serviceDuration;
                    if ($this->isTimeSlotAvailable($gapStart, $potentialSlotEnd, $reservedSlots, $breakTimes)) {
                        $availableSlots[] = [
                            'start' => $this->minutesToTime($gapStart),
                            'end' => $this->minutesToTime($potentialSlotEnd)
                        ];
                        // error_log("Added optimized slot: " . $this->minutesToTime($gapStart) . " - " . $this->minutesToTime($potentialSlotEnd));
                        return; // Exit after adding the first available optimized slot
                    }
                    $gapStart += 5; // Move in 5-minute increments
                }
            }
        }
    }

    private function roundToNearestSlot(int $minutes): int
    {
        return ceil($minutes / $this->slotDuration) * $this->slotDuration;
    }

    private function roundUpToNearest5Minutes(int $minutes): int
    {
        return ceil($minutes / 5) * 5;
    }

    private function isTimeSlotAvailable(int $start, int $end, array $reservedSlots, array $breakTimes): bool
    {
        // Check if the slot overlaps with any reserved slots
        foreach ($reservedSlots as $slot) {
            if ($start < $slot['end'] && $end > $slot['start']) {
                // error_log("Slot {$this->minutesToTime($start)} - {$this->minutesToTime($end)} overlaps with reserved slot {$this->minutesToTime($slot['start'])} - {$this->minutesToTime($slot['end'])}");
                return false;
            }
        }

        // Check if any part of the service duration overlaps with any break time
        foreach ($breakTimes as $break) {
            if (($start < $break['end'] && $end > $break['start']) ||
                ($start >= $break['start'] && $start < $break['end']) ||
                ($end > $break['start'] && $end <= $break['end'])
            ) {
                // error_log("Service duration {$this->minutesToTime($start)} - {$this->minutesToTime($end)} overlaps with break time {$this->minutesToTime($break['start'])} - {$this->minutesToTime($break['end'])}");
                return false;
            }
        }

        return true;
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

    private function isNewAppointmentOverlapping(int $start, int $duration, array $reservedSlots): bool
    {
        $end = $start + $duration;

        foreach ($reservedSlots as $slot) {
            if ($start < $slot['end'] && $end > $slot['start']) {
                return true;
            }
        }

        return false;
    }


    private function isValidDate(string $date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function getBusinessHours(string $date): array
    {
        if (!isset($this->cache['business_hours'][$date])) {
            $dayOfWeek = strtolower(date('l', strtotime($date)));
            $openTime = get_option("{$dayOfWeek}_open_time", get_option('open_time', '09:00'));
            $closeTime = get_option("{$dayOfWeek}_close_time", get_option('close_time', '17:00'));

            $this->cache['business_hours'][$date] = [
                'openTime' => $openTime,
                'closeTime' => $closeTime
            ];
        }
        return $this->cache['business_hours'][$date];
    }

    private function getBreakTimes(string $date): array
    {
        if (!isset($this->cache['break_times'][$date])) {
            $dayOfWeek = strtolower(date('l', strtotime($date)));
            $breakStart = get_option("{$dayOfWeek}_break_start", get_option('break_start'));
            $breakEnd = get_option("{$dayOfWeek}_break_end", get_option('break_end'));

            if ($breakStart && $breakEnd) {
                $this->cache['break_times'][$date] = [
                    [
                        'start' => $this->timeToMinutes($breakStart),
                        'end' => $this->timeToMinutes($breakEnd)
                    ]
                ];
            } else {
                $this->cache['break_times'][$date] = [];
            }
        }
        return $this->cache['break_times'][$date];
    }

    private function getBufferTime(): int
    {
        return (int)get_option('buffer_time', 5);
    }

    private function getSlotDuration(): int
    {
        return (int)get_option('time_slot_duration', 30);
    }

    public function getReservedTimeSlots(string $date): array
    {
        if (!isset($this->cache['reserved_slots'][$date])) {
            if (!$this->isValidDate($date)) {
                throw new InvalidArgumentException("Invalid date format. Expected Y-m-d.");
            }

            try {
                $query = $this->wpdb->prepare(
                    "SELECT startTime, endTime 
                    FROM {$this->appointments_table} 
                    WHERE date = %s AND status != 'cancelled'
                    ORDER BY startTime",
                    $date
                );

                $results = $this->wpdb->get_results($query);

                if ($results === false) {
                    throw new Exception("Error executing database query: " . $this->wpdb->last_error);
                }

                $slots = array_map(function ($slot) {
                    return [
                        'start' => $this->timeToMinutes($slot->startTime),
                        'end' => $this->timeToMinutes($slot->endTime) + $this->bufferTime
                    ];
                }, $results);

                $this->cache['reserved_slots'][$date] = $this->mergeOverlappingSlots($slots);
            } catch (Exception $e) {
                error_log("Error retrieving reserved time slots: " . $e->getMessage());
                return [];
            }
        }

        return $this->cache['reserved_slots'][$date];
    }

    private function mergeOverlappingSlots(array $slots): array
    {
        if (empty($slots)) {
            return [];
        }

        usort($slots, function ($a, $b) {
            return $a['start'] - $b['start'];
        });

        $merged = [$slots[0]];

        for ($i = 1; $i < count($slots); $i++) {
            $current = $slots[$i];
            $last = &$merged[count($merged) - 1];

            if ($current['start'] <= $last['end']) {
                $last['end'] = max($last['end'], $current['end']);
            } else {
                $merged[] = $current;
            }
        }

        return $merged;
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

    public function isSlotAvailable(string $date, string $startTime, int $duration): bool
    {
        try {
            $businessHours = $this->getBusinessHours($date);
            $breakTimes = $this->getBreakTimes($date);
            $reservedSlots = $this->getReservedTimeSlots($date);

            $startMinutes = $this->timeToMinutes($startTime);
            $endMinutes = $startMinutes + $duration;

            $openTime = $this->timeToMinutes($businessHours['openTime']);
            $closeTime = $this->timeToMinutes($businessHours['closeTime']);

            // Check if the slot fits within business hours
            if ($startMinutes < $openTime || $endMinutes > $closeTime) {
                return false;
            }

            return $this->isTimeSlotAvailable($startMinutes, $endMinutes, $reservedSlots, $breakTimes);
        } catch (Exception $e) {
            error_log("Error checking slot availability: " . $e->getMessage());
            return false;
        }
    }

    public function getNextAvailableSlot(string $date, int $duration): ?array
    {
        try {
            $businessHours = $this->getBusinessHours($date);
            $breakTimes = $this->getBreakTimes($date);
            $reservedSlots = $this->getReservedTimeSlots($date);

            $openTime = $this->timeToMinutes($businessHours['openTime']);
            $closeTime = $this->timeToMinutes($businessHours['closeTime']);

            $currentTime = $openTime;

            while ($currentTime + $duration <= $closeTime) {
                if ($this->isTimeSlotAvailable($currentTime, $currentTime + $duration, $reservedSlots, $breakTimes)) {
                    return [
                        'start' => $this->minutesToTime($currentTime),
                        'end' => $this->minutesToTime($currentTime + $duration)
                    ];
                }
                $currentTime += $this->slotDuration;
            }

            return null; // No available slot found
        } catch (Exception $e) {
            error_log("Error finding next available slot: " . $e->getMessage());
            return null;
        }
    }
}
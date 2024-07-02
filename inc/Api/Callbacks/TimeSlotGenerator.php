<?php

namespace Inc\Api\Callbacks;

class TimeSlotGenerator
{
    private $wpdb;
    private $appointments_table;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->appointments_table = $wpdb->prefix . 'am_appointments';
    }

    public function generateAvailableTimeSlots(array $params): array
    {
        $date = $params['date'];
        $serviceDuration = $params['serviceDuration'];

        $businessHours = $this->getBusinessHours();
        $breakTime = $this->getBreakTime();
        $reservedSlots = $this->getReservedTimeSlots($date);
        $minSlotDuration = $this->getMinSlotDuration();

        $openTime = $this->timeToMinutes($businessHours['openTime']);
        $closeTime = $this->timeToMinutes($businessHours['closeTime']);
        $breakStart = $breakTime ? $this->timeToMinutes($breakTime['start']) : null;
        $breakEnd = $breakTime ? $this->timeToMinutes($breakTime['end']) : null;

        $availableSlots = [];
        $currentTime = $openTime;

        while ($currentTime < $closeTime) {
            // Check if current time is within break time
            if ($breakStart && $breakEnd && $currentTime >= $breakStart && $currentTime < $breakEnd) {
                $currentTime = $breakEnd;
                continue;
            }

            // Check if the current time slot overlaps with any reserved slot
            $isReserved = $this->isTimeSlotReserved($currentTime, $currentTime + $serviceDuration, $reservedSlots);

            if (!$isReserved) {
                $slotEnd = $currentTime + $serviceDuration;
                $availableSlots[] = [
                    'start' => $this->minutesToTime($currentTime),
                    'end' => $this->minutesToTime($slotEnd)
                ];
            }

            // Move to the next potential slot start time
            $currentTime += $minSlotDuration;
        }

        return $availableSlots;
    }

    private function isTimeSlotReserved(int $start, int $end, array $reservedSlots): bool
    {
        foreach ($reservedSlots as $slot) {
            if (($start < $slot['end'] && $end > $slot['start']) || 
                ($start >= $slot['start'] && $start < $slot['end']) || 
                ($end > $slot['start'] && $end <= $slot['end'])) {
                return true;
            }
        }
        return false;
    }

    private function getBusinessHours(): array
    {
        $openTime = get_option('open_time', '09:00');
        $closeTime = get_option('close_time', '17:00');

        return [
            'openTime' => $openTime,
            'closeTime' => $closeTime
        ];
    }

    private function getBreakTime(): ?array
    {
        $breakStart = get_option('break_start', null);
        $breakEnd = get_option('break_end', null);

        if ($breakStart && $breakEnd) {
            return [
                'start' => $breakStart,
                'end' => $breakEnd
            ];
        }

        return null;
    }

    private function getMinSlotDuration(): int
    {
        return max(15, get_option('min_slot_duration', 15)); // Minimum 15 minutes
    }

    public function getReservedTimeSlots(string $date): array
    {
        $query = $this->wpdb->prepare(
            "SELECT startTime, endTime FROM {$this->appointments_table} WHERE date = %s ORDER BY startTime",
            $date
        );
        $results = $this->wpdb->get_results($query);

        return array_map(function($slot) {
            return [
                'start' => $this->timeToMinutes($slot->startTime),
                'end' => $this->timeToMinutes($slot->endTime)
            ];
        }, $results);
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
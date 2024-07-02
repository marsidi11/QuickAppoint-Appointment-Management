<template>

    <div class="calendar-time">

        <h2 class="calendar-time-header">Select a Time</h2>

        <div v-if="loading" class="loading-style-1"></div>

        <div :class="['calendar-time-body', gridColumnsClass]">

            <div class="calendar-time-row" v-for="time in times" :key="time" @click="selectTime(time)">

                <div class="calendar-time-item" :class="{ 'selected-time': time === selectedTime }">
                    {{ time }}
                </div>

            </div>

        </div>

    </div>

    <div class="calendar-nav">
        <button class="nav-previous" @click="$emit('prev-clicked')">Go Back</button>
        <button class="nav-next" @click="nextClicked">Next</button>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

</template>

<script>
import { getOpenTime, getCloseTime, getTimeSlotDuration, getBreakStart, getBreakEnd, getReservedTimeSlots } from './apiService';

export default {
    name: 'CalendarTime',

    props: {
        selectedDate: {
            type: Date,
            required: true
        },
    },

    data() {
        return {
            times: [],
            selectedTime: null,
            errorMessage: null,
            loading: false,
        };
    },

    computed: {
        formattedSelectedDate() {
            const date = new Date(this.selectedDate);
            const year = date.getFullYear();
            const month = ("0" + (date.getMonth() + 1)).slice(-2);
            const day = ("0" + date.getDate()).slice(-2); 

            return `${year}-${month}-${day}`;
        },

        gridColumnsClass() {
            switch (this.times.length) {
                case 1:
                    return 'grid-cols-1';
                case 2:
                    return 'grid-cols-2';
                case 3:
                    return 'grid-cols-3';
                default:
                    return 'grid-cols-3 sm:grid-cols-4 xl:grid-cols-5';
            }
        }
    },

    methods: {

        // Get Open Time
        async fetchOpenTime() {
            try {
                this.loading = true;
                const response = await getOpenTime();
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get Close Time
        async fetchCloseTime() {
            try {
                const response = await getCloseTime();
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get Time Slot Duration
        async fetchTimeSlotDuration() {
            try {
                const response = await getTimeSlotDuration();
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get Break Start Time
        async fetchBreakStart() {
            try {
                const response = await getBreakStart();

                if (response === null) {
                    return 0;
                }
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get Break End Time
        async fetchBreakEnd() {
            try {
                const response = await getBreakEnd();

                if (response === null) {
                    return 0;
                }
                return response;

            } catch (error) {
                this.errorMessage = error;
            } 
        },

        // Get Reserved Time Slots
        async fetchReservedTimeSlots() {
            try {
                const response = await getReservedTimeSlots(this.formattedSelectedDate);

                if (response.length === 0) {
                    return [];
                }
                return response;

            } catch (error) {
                this.errorMessage = error;
            } finally {
                this.loading = false;
            }
        },

        // Generate Times from Open Time to Close Time, excluding reserved time slots and break time
        // TODO: Check if total time of services selected is lower than slot duration so in it can be displayed in those cases
        async generateTimes() {
            const openTimeString = await this.fetchOpenTime();
            const closeTimeString = await this.fetchCloseTime();
            const timeSlotDuration = parseInt(await this.fetchTimeSlotDuration());
            const breakStartTimeString = await this.fetchBreakStart();
            const breakEndTimeString = await this.fetchBreakEnd();
            const reservedTimeSlots = await this.fetchReservedTimeSlots();


            const openTimeParts = openTimeString.split(':').map(Number);
            const closeTimeParts = closeTimeString.split(':').map(Number);
            const breakStartParts = breakStartTimeString ? breakStartTimeString.split(':').map(Number) : null;
            const breakEndParts = breakEndTimeString ? breakEndTimeString.split(':').map(Number) : null;

            const times = [];

            let currentMinutes = openTimeParts[0] * 60 + openTimeParts[1];
            const closeMinutes = closeTimeParts[0] * 60 + closeTimeParts[1];
            const breakStartMinutes = breakStartParts ? breakStartParts[0] * 60 + breakStartParts[1] : null;
            const breakEndMinutes = breakEndParts ? breakEndParts[0] * 60 + breakEndParts[1] : null;

            while (currentMinutes < closeMinutes) {
                const endOfTimeSlot = currentMinutes + timeSlotDuration;

                if (breakStartMinutes !== null && breakEndMinutes !== null) {
                    if (currentMinutes < breakEndMinutes && endOfTimeSlot > breakStartMinutes) {
                        currentMinutes = breakEndMinutes;
                        continue;
                    }
                }

                // Check if the current time slot is reserved
                const isReserved = reservedTimeSlots.some(slot => {
                    const start = slot.startTime.split(':').map(Number);
                    const end = slot.endTime.split(':').map(Number);
                    const startMinutes = start[0] * 60 + start[1];
                    const endMinutes = end[0] * 60 + end[1];
                    return currentMinutes < endMinutes && endOfTimeSlot > startMinutes;
                });

                // If the current time slot is not reserved, add it to the list
                if (!isReserved) {
                    const hours = Math.floor(currentMinutes / 60).toString().padStart(2, '0');
                    const minutes = (currentMinutes % 60).toString().padStart(2, '0');
                    times.push(`${hours}:${minutes}`);
                }

                currentMinutes += timeSlotDuration;
            }

            this.times = times;
        },

        // Emit selected time
        selectTime(time) {
            this.selectedTime = time;
            this.$emit('time-selected', time);
        },

        nextClicked() {
            if (this.selectedTime !== null) {
                this.$emit('next-clicked');
            } else {
                this.errorMessage = 'Please select a time';
            }
        },
    },

    created() {
        this.generateTimes();
    },
}
</script>
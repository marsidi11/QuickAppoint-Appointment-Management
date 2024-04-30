<template>

    <div class="calendar-time">

        <h2 class="calendar-time-header">Select a Time</h2>

        <div v-if="loading" class='flex space-x-2 justify-center items-center bg-white dark:invert'>
            <span class='sr-only'>Loading...</span>
            <div class='h-8 w-8 bg-black rounded-full animate-bounce [animation-delay:-0.3s]'></div>
            <div class='h-8 w-8 bg-black rounded-full animate-bounce [animation-delay:-0.15s]'></div>
            <div class='h-8 w-8 bg-black rounded-full animate-bounce'></div>
        </div>

        <div class="calendar-time-body">

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
import { getOpenTime, getCloseTime } from './apiService';

export default {
    name: 'CalendarTime',

    data() {
        return {
            times: [],
            selectedTime: null,
            errorMessage: null,
            loading: false,
        };
    },

    methods: {

        // Get Open Time
        async fetchOpenTime() {
            try {
                this.loading = true;
                const response = await getOpenTime();
                console.log("Get Open Time: ", JSON.stringify(response, null, 2));
                return response;

            } catch (error) {
                this.errorMessage = error;
            } finally {
                this.loading = false;
            }
        },

        // Get Close Time
        async fetchCloseTime() {
            try {
                const response = await getCloseTime();
                console.log("Get Close Time: ", JSON.stringify(response, null, 2));
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Generate Times from Open Time to Close Time
        async generateTimes() {
            const openTimeString = await this.fetchOpenTime();
            const closeTimeString = await this.fetchCloseTime();

            const openTimeParts = openTimeString.split(':').map(Number);
            const closeTimeParts = closeTimeString.split(':').map(Number);

            const times = [];

            for (let hour = openTimeParts[0]; hour < closeTimeParts[0]; hour++) {
                times.push(`${hour.toString().padStart(2, '0')}:00`);
            }
            
            console.log("Times: ", times);
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

    // TODO: Call generateTimes() method when index.vue is created
    created() {
        this.generateTimes();
    },
}
</script>
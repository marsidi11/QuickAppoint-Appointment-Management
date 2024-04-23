<template>

    <div class="calendar-time">

        <h2 class="calendar-time-header">Select a Time</h2>

        <div class="calendar-time-body">
            
            <div class="calendar-time-row" v-for="time in times" :key="time" @click="selectTime(time)">
                <div class="calendar-time-item">{{ time }}</div>
            </div>

        </div>

    </div>

</template>

<script>
import apiService from './apiService';

export default {
    name: 'CalendarTime',

    data() {
        return {
            times: [],
            selectedTime: null,
        };
    },

    methods: {

        // Get Open Time
        async getOpenTime() {
            try {
                const response = await apiService.getOpenTime();
                console.log("Get Open Time: ", JSON.stringify(response, null, 2));
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get Close Time
        async getCloseTime() {
            try {
                const response = await apiService.getCloseTime();
                console.log("Get Close Time: ", JSON.stringify(response, null, 2));
                return response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Generate Times from Open Time to Close Time
        async generateTimes() {
            const openTimeString = await this.getOpenTime();
            const closeTimeString = await this.getCloseTime();

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
    },

    // TODO: Call generateTimes() method when index.vue is created
    // Call generateTimes() method when component is created
    created() {
        this.generateTimes();
    },
}
</script>
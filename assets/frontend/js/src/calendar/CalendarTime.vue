<template>
    <div class="calendar-time">
        <div class="calendar-time-header">
            <h2 class="calendar-time-header-title">Select a Time</h2>
        </div>
        <div class="calendar-time-body">
            <div class="calendar-time-row" v-for="time in times" :key="time">
                <div class="calendar-time-item">{{ time }}</div>
                <div class="calendar-time-item"></div>
            </div>
        </div>
        <div class="button-handler">
            <button @click="getBooking">Get Booking</button>
            <button @click="createBooking">Create Booking</button>
        </div>
    </div>
</template>

<script>
import DataHandler from './DataHandler.vue';

export default {
    name: 'CalendarTime',

    methods: {
        getBooking() {
            DataHandler.methods.getBooking();
        },
        createBooking() {
            DataHandler.methods.createBooking();
        }
    },
    
    data() {
        return {
            times: [
                '00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'
            ]
        }
    }
}
</script>
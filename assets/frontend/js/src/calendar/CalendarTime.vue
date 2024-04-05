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

export default {
    name: 'CalendarTime',

    data() {
        return {
            times: [
                '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'
            ],
            selectedTime: null,
        };
    },

    methods: {
        selectTime(time) {
            this.selectedTime = time;
            this.$emit('time-selected', time);
        },
    },
}
</script>
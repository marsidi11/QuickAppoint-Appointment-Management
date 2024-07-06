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
import { getAvailableTimeSlots } from './apiService';

export default {
    name: 'CalendarTime',

    props: {
        selectedDate: {
            type: Date,
            required: true
        },
        servicesDuration: {
            type: Number,
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

        async availableTimeSlots() {
            try {
                this.loading = true;
                const response = await getAvailableTimeSlots(this.formattedSelectedDate, this.servicesDuration);

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

        async fetchAndSetTimes() {
            const availableSlots = await this.availableTimeSlots();
            this.times = availableSlots.map(slot => slot.start);
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
        this.fetchAndSetTimes();
    },

}
</script>
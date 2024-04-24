<template>
    <div class="calendar-body">

        <div class="calendar-week">
            <div class="calendar-day" v-for="day in daysOfWeek" :key="day">{{ day }}</div>
        </div>

        <div class="calendar-week" v-for="(week, index) in calendar" :key="index">

            <div
                class="calendar-day"
                v-for="(day, dayIndex) in week"
                :key="`${index}-${dayIndex}`"
                :class="dayClasses(day)"
                @click="dayClicked(day.date)"
            >
                {{ day.date.getDate() }}
            </div>
            
        </div>

    </div>
</template>

<script>
import { isCurrentDay, isPastDate, isDateWithinAllowedRange, dayClicked, isDateWithinNextXDays } from './CalendarUtils.js';
import { getDatesRange } from './apiService';

export default {
    name: 'CalendarBody',

    data() {
        return {
            selectedDate: null,
            datesRange: null, 
        };
    },

    props: {
        currentDate: {
            type: Date,
            required: true
        },
        daysOfWeek: {
            type: Array,
            required: true
        },
        calendar: {
            type: Array,
            required: true
        }
    },
    methods: {
        isCurrentDay,
        isPastDate,
        isDateWithinAllowedRange,

        async fetchDatesRange() {
            try {
                const response = await getDatesRange();
                console.log("Get Dates Range: ", JSON.stringify(response, null, 2));
                this.datesRange = response; // Set datesRange to response (allowed next x booking days)

            } catch (error) {
                this.errorMessage = error;
            }
        },

        dayClasses(day) {
            return {
                'current-day': this.isCurrentDay(day.date),
                'prev-month-day': day.date.getMonth() < this.currentDate.getMonth(),
                'next-month-day': day.date.getMonth() > this.currentDate.getMonth(),
                'past-day': this.isPastDate(day.date),
                'clickable-day': !this.isPastDate(day.date) && this.isDateWithinAllowedRange(day.date, this.datesRange) && day.date.getMonth() === this.currentDate.getMonth(),
                'selected-day': this.selectedDate && day.date.getTime() === this.selectedDate.getTime(),
            };
        },

        dayClicked(date) {
            // TODO: Get numberOfDays option from the backend
            let numberOfDays = this.datesRange;

            if (isPastDate(date)) {
                return null;
            }
            if (!isDateWithinNextXDays(date, numberOfDays)) {
                return null;
            }
            this.selectedDate = date;
            this.$emit('dateSelected', date);
        }
    },

    // TODO: Call fetchDatesRange() method when index.vue is created
    created() {
        this.fetchDatesRange();
    }
}
</script>
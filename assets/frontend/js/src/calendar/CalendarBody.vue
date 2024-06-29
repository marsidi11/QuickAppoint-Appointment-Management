<template>
    <div class="calendar-wrapper">
        <button class="reset-month" @click="resetMonth" :disabled="isCurrentMonth">Current Month</button>
        <div class="calendar-header">
            <button class="prev-month-icon" @click="prevMonth" :disabled="isCurrentMonth">&lt;</button>
            <h2 class="current-month">{{ currentMonth }}</h2>
            <button class="next-month-icon" @click="nextMonth">&gt;</button>
        </div>

        <div class="calendar-body">
            <div class="calendar-week">
                <div class="calendar-day-name" v-for="day in daysOfWeek" :key="day">{{ day }}</div>
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
    </div>
    
    <div class="calendar-nav">
        <button class="nav-next" @click="nextClicked">Next</button>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

</template>

<script>
import { isCurrentDay, isPastDate, isDateWithinAllowedRange, isOpenDay, isDateWithinNextXDays } from './CalendarUtils.js';
import { getDatesRange, getOpenDays } from './apiService';

export default {
    name: 'CalendarBody',

    data() {
        return {
            selectedDate: null,
            datesRange: null, 
            openDays: [],
            errorMessage: null,
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
        },
        currentMonth: {
            type: String,
            required: true
        }
    },

    computed: {
        isCurrentMonth() {
            return this.currentDate.getMonth() === new Date().getMonth();
        }
    },

    methods: {
        isCurrentDay,
        isPastDate,
        isDateWithinAllowedRange,
        isOpenDay,
        isDateWithinNextXDays,

        resetMonth() {
            this.$emit('reset-month');
        },
        prevMonth() {
            this.$emit('prev-month');
        },
        nextMonth() {
            this.$emit('next-month');
        },

        async fetchDatesRange() {
            try {
                const response = await getDatesRange();
                this.datesRange = response; // Set datesRange to response (allowed next x booking days)

            } catch (error) {
                this.errorMessage = error;
            }
        },

        async fetchOpenDays() {
            try {
                const response = await getOpenDays();
                this.openDays = response; // Set openDays to response (days when the business is open)

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
                'clickable-day': !this.isPastDate(day.date) && this.isDateWithinAllowedRange(day.date, this.datesRange) && this.isOpenDay(day.date, this.openDays),
                'selected-day': this.selectedDate && day.date.getTime() === this.selectedDate.getTime(),
            };
        },

        dayClicked(date) {
            if (this.isValidDate(date)) {
                this.selectedDate = date;
                this.$emit('date-selected', date);
                this.handleMonthTransition(date);
            }
        },

        nextClicked() { // Emit an event when the Next button is clicked
            if (this.selectedDate !== null) {
                this.$emit('next-clicked');
            } else {
                this.errorMessage = 'Please select a date';
            }
        },

        isValidDate(date) {
            return !this.isPastDate(date) &&
                this.isDateWithinNextXDays(date, this.datesRange) &&
                this.isOpenDay(date, this.openDays);
        },

        handleMonthTransition(date) {
            if (date.getMonth() > this.currentDate.getMonth() ||
                (date.getMonth() === 0 && this.currentDate.getMonth() === 11)) {
                this.$emit('next-month');
            }

            if (date.getMonth() < this.currentDate.getMonth() ||
                (date.getMonth() === 11 && this.currentDate.getMonth() === 0)) {
                this.$emit('prev-month');
            }
        },
    },

    // TODO: Call methods when index.vue is created
    created() {
        this.fetchDatesRange();
        this.fetchOpenDays();
    }
}
</script>
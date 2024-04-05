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
                :class="{
                    'current-day': isCurrentDay(day.date),
                    'prev-month-day': day.date.getMonth() < currentDate.getMonth(),
                    'next-month-day': day.date.getMonth() > currentDate.getMonth(),
                    'past-day': isPastDate(day.date),
                    'clickable-day': !isPastDate(day.date) && isDateWithinAllowedRange(day.date),
                }"
                @click="dayClicked(day.date)"
            >
                {{ day.date.getDate() }}
            </div>
            
        </div>

    </div>
</template>

<script>
import { isCurrentDay, isPastDate, isDateWithinAllowedRange, dayClicked } from './CalendarUtils.js';

export default {
    name: 'CalendarBody',

    data() {
        return {
            selectedDate: null,
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
        dayClicked,
    }
}
</script>
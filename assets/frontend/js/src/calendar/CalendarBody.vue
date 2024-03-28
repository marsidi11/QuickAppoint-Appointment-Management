<template>
    <div class="calendar-body">
        <div class="calendar-week">
            <div class="calendar-days" v-for="day in daysOfWeek" :key="day">{{ day }}</div>
        </div>
        <div class="calendar-week" v-for="(week, index) in calendar" :key="index">
            <div
                class="calendar-days"
                v-for="(day, dayIndex) in week"
                :key="`${index}-${dayIndex}`"
                :class="{
                    'current-day': isCurrentDay(day.date),
                    'prev-month-days': day.date.getMonth() < currentDate.getMonth(),
                    'next-month-days': day.date.getMonth() > currentDate.getMonth(),
                    'past-days': pastDates(calendar).some(pastDate => pastDate.date.getTime() === day.date.getTime()),
                }"
            >
                {{ day.date.getDate() }}
            </div>
        </div>
    </div>
</template>

<script>
import { isCurrentDay, pastDates } from './CalendarUtils.js';

export default {
    name: 'CalendarBody',
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
        pastDates
    }
}
</script>
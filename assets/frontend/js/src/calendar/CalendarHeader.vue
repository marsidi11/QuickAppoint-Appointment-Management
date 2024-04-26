<template>
    <button class="reset-month" @click="resetMonth">View Current Date</button>
    <div class="calendar-header">
        <button class="prev-month-icon" @click="prevMonth">&lt;</button>
        <h2 class="current-month">{{ currentMonth }}</h2>
        <button class="next-month-icon" @click="nextMonth">&gt;</button>
    </div>
</template>

<script>
export default {
    name: 'CalendarHeader',
    props: {
        currentMonth: {
            type: String,
            required: true
        }
    },
    methods: {
        resetMonth() {
            this.$emit('reset-month');
        },
        prevMonth() {
            this.$emit('prev-month');
        },
        nextMonth() {
            this.$emit('next-month');
        }
    }
}
</script>
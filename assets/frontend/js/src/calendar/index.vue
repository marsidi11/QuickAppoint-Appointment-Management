<template>
    <div class="calendar-container">
        <div class="calendar-header">
            <button @click="prevMonth">&lt;</button>
            <h2>{{ currentMonth }}</h2>
            <button @click="nextMonth">&gt;</button>
        </div>
        <div class="calendar-body">
            <div class="calendar-week">
                <div class="calendar-day" v-for="day in daysOfWeek" :key="day">{{ day }}</div>
            </div>
            <div class="calendar-week" v-for="week in calendar" :key="week">
                <div class="calendar-day" v-for="day in week" :key="day.date" :class="{ 'current-day': isCurrentDay(day.date) }">
                    {{ day.date.getDate() }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CalendarComponent',
    data() {
        return {
            currentDate: new Date(),
            daysOfWeek: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
        }
    },
    computed: {
        currentMonth() {
            return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
        },
        calendar() {
            const date = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
            const days = [];
            while (date.getMonth() === this.currentDate.getMonth()) {
                const week = [];
                for (let i = 0; i < 7; i++) {
                    week.push({ date: new Date(date) });
                    date.setDate(date.getDate() + 1);
                }
                days.push(week);
            }
            return days;
        }
    },
    methods: {
        prevMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
        },
        nextMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
        },
        isCurrentDay(date) {
            const today = new Date();
            return date.getDate() === today.getDate() && date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear();
        }
    },
    mounted() {
        // Fetch data or perform any necessary operations when the component is mounted
        // You can access the localized data from WordPress using `window.yourPluginData`
        console.log(window.yourPluginData);
    }
}
</script>

<!-- <style scoped>
.calendar-container {
    /* Add your calendar container styles */
}
.calendar-header {
    /* Add your calendar header styles */
}
.calendar-body {
    /* Add your calendar body styles */
}
.calendar-week {
    /* Add your calendar week styles */
}
.calendar-day {
    /* Add your calendar day styles */
}
.current-day {
    /* Add your current day styles */
}
</style> -->
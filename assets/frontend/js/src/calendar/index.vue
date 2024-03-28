<template>
    <div class="calendar-container">
    <div class="calendar-header">
        <button class="prev-month-icon" @click="prevMonth">&lt;</button>
        <h2 class="current-month">{{ currentMonth }}</h2>
        <button class="next-month-icon" @click="nextMonth">&gt;</button>
    </div>
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
            'prev-month-days': day.date.getMonth() < currentDate.getMonth(),
            'next-month-days': day.date.getMonth() > currentDate.getMonth(),
            }"
        >
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
      daysOfWeek: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 
      startDay: 1, // Monday is 1
    }
  },
	computed: {

    currentMonth() {
      	return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
	},

    calendar() {
		const date = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
		const days = [];
		const firstDayOfMonth = (date.getDay() + 6) % 7;

		// Add days from the previous month to the current calendar
		for (let i = firstDayOfMonth; i > 0; i--) {
			const prevMonthDay = new Date(date);
			prevMonthDay.setDate(prevMonthDay.getDate() - i);
			days.unshift({ date: prevMonthDay });
		}

		// Add days of the current month
		while (date.getMonth() === this.currentDate.getMonth()) {
			days.push({ date: new Date(date) });
			date.setDate(date.getDate() + 1);
		}
		// Add days from the next month to the current calendar
		const lastDayOfMonth = days[days.length - 1].date.getDay();
		let daysToAdd = 0;
		// Determine the number of days to add
		if (lastDayOfMonth !== 0) {
			daysToAdd = 7 - lastDayOfMonth;
		}
		for (let i = 1; i <= daysToAdd; i++) {
			const nextMonthDay = new Date(days[days.length - 1].date);
			nextMonthDay.setDate(nextMonthDay.getDate() + 1);
			days.push({ date: nextMonthDay });
		}

		// Group days into weeks
		const weeks = [];
		for (let i = 0; i < days.length; i += 7) {
			weeks.push(days.slice(i, i + 7));
		}
		return weeks;
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
			return (
			date.getDate() === today.getDate() &&
			date.getMonth() === today.getMonth() &&
			date.getFullYear() === today.getFullYear()
			);
		}
  },
  mounted() {
	// Fetch data or perform any necessary operations when the component is mounted
	// You can access the localized data from WordPress using `window.yourPluginData`
	console.log(window.yourPluginData);
  }
}
</script>
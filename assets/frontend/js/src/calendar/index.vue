<template>
	<div class="calendar-container">
		<CalendarHeader :currentMonth="currentMonth" @prev-month="prevMonth" @next-month="nextMonth" />
		<CalendarBody :currentDate="currentDate" :daysOfWeek="daysOfWeek" :calendar="calendar" />
		<CalendarTime />
	</div>
</template>

<script>
import { generateCalendar } from './CalendarUtils.js';
import CalendarHeader from './CalendarHeader.vue';
import CalendarBody from './CalendarBody.vue';
import CalendarTime from './CalendarTime.vue';

export default {
	name: 'CalendarComponent',
	components: {
		CalendarHeader,
		CalendarBody
	},
	data() {
		return {
			currentDate: new Date(),
			daysOfWeek: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 
			startDay: 1,
			selectedDate: null,
		}
	},
	computed: {
		currentMonth() {
			return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
		},
		calendar() {
			return generateCalendar(this.currentDate);
		},
	},
	methods: {
		prevMonth() {
			this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
		},
		nextMonth() {
			this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
		},
	},
	mounted() {
		console.log(window.yourPluginData);
	}
}
</script>
<template>
	<div class="calendar-container">
		<CalendarHeader :currentMonth="currentMonth" @prev-month="prevMonth" @next-month="nextMonth" />
		<CalendarBody :currentDate="currentDate" :daysOfWeek="daysOfWeek" :calendar="calendar" @date-selected="showCalendarTime" />
		<CalendarTime v-if="showCalendarTimeComponent" :selected-date="selectedDate" />
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
		CalendarBody,
		CalendarTime,
	},

	data() {
		return {
			currentDate: new Date(),
			daysOfWeek: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 
			selectedDate: null,
			showCalendarTimeComponent: false,
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

		// Show the CalendarTime component when a date is selected
		showCalendarTime(date) {
			this.showCalendarTimeComponent = true;
			this.selectedDate = date;
		},
	},

	mounted() {
		console.log(window.yourPluginData);
	}
}
</script>
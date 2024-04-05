<template>
	<div class="calendar-container">

		<CalendarHeader :currentMonth="currentMonth" @prev-month="prevMonth" @next-month="nextMonth" />

		<CalendarBody :currentDate="currentDate" :daysOfWeek="daysOfWeek" :calendar="calendar" @date-selected="showCalendarServices" />

		<CalendarServices v-if="showCalendarServicesComponent" :selected-date="selectedDate" @services-selected="showCalendarTime" />

		<CalendarTime v-if="showCalendarTimeComponent" :selected-services="selectedServices" @time-selected="showCalendarUserData" />

		<CalendarUserData v-if="showCalendarUserDataComponent" :selected-time="selectedTime" @create-booking="createBooking" />

	</div>
</template>

<script>
import { generateCalendar } from './CalendarUtils.js'; 
import CalendarHeader from './CalendarHeader.vue';
import CalendarBody from './CalendarBody.vue';
import CalendarServices from './CalendarServices.vue';
import CalendarTime from './CalendarTime.vue';
import CalendarUserData from './CalendarUserData.vue';


export default {
	name: 'CalendarComponent',

	components: {
		CalendarHeader,
		CalendarBody,
		CalendarServices,
		CalendarTime,
		CalendarUserData,
	},

	data() {
		return {
			currentDate: new Date(),
			daysOfWeek: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 
			showCalendarServicesComponent: false,
			showCalendarTimeComponent: false,
			showCalendarUserDataComponent: false,
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

		// Show the CalendarServices component when a date is selected
		showCalendarServices(date) {
			this.showCalendarServicesComponent = true;
			this.selectedDate = date;
			console.log("Date: " + this.selectedDate);
		},

		// Show the CalendarTime component when a service is selected
		showCalendarTime(services) {
			this.showCalendarTimeComponent = true;
			this.selectedServices = services;
			console.log("Service: " + this.selectedServices);
		},

		// Show the CalendarUserData component when a time is selected
		showCalendarUserData(time) {
			this.showCalendarUserDataComponent = true;
			this.selectedTime = time;
			console.log("Time: " + this.selectedTime);
		},
	},

	mounted() {
		console.log(window.yourPluginData);
	}
}
</script>
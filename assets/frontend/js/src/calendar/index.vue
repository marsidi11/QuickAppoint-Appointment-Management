<template>
	<div class="calendar-container">

		<CalendarHeader :currentMonth="currentMonth" @prev-month="prevMonth" @next-month="nextMonth" />

		<CalendarBody :currentDate="currentDate" :daysOfWeek="daysOfWeek" :calendar="calendar" @date-selected="showCalendarServices" />

		<CalendarServices v-if="showCalendarServicesComponent" :selected-date="selectedDate" @servicesSelected="showCalendarTime" />

		<CalendarTime v-if="showCalendarTimeComponent" :selected-services="selectedServices" @time-selected="showCalendarUserData" />

		<CalendarUserData v-if="showCalendarUserDataComponent" :selected-time="selectedTime" @update-user-data="storeUserData" />

		<CreateAppointment v-if="showCalendarUserDataComponent" :appointment-data="appointmentData" />

	</div>
</template>

<script>
import { generateCalendar, calculateEndTime } from './CalendarUtils.js'; 
import CalendarHeader from './CalendarHeader.vue';
import CalendarBody from './CalendarBody.vue';
import CalendarServices from './CalendarServices.vue';
import CalendarTime from './CalendarTime.vue';
import CalendarUserData from './CalendarUserData.vue';
import CreateAppointment from './CreateAppointment.vue';


export default {
	name: 'CalendarComponent',

	components: {
		CalendarHeader,
		CalendarBody,
		CalendarServices,
		CalendarTime,
		CalendarUserData,
		CreateAppointment,
	},

	data() {
		return {
			currentDate: new Date(),
			daysOfWeek: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 

			showCalendarServicesComponent: false,
			showCalendarTimeComponent: false,
			showCalendarUserDataComponent: false,

			userData: {
                name: '',
                surname: '',
                phone: '',
                email: ''
            },
		}
	},

	computed: {
		currentMonth() {
			return this.currentDate.toLocaleString('default', { month: 'long', year: 'numeric' });
		},

		calendar() {
			return generateCalendar(this.currentDate);
		},

		appointmentData() {
			const service_ids = this.selectedServices.map(service => service.id);
			const service_durations = this.selectedServices.map(service => service.duration);

			const end_time = calculateEndTime(this.selectedTime, this.selectedDate, service_durations);

			return {
				name: this.userData.name,
				surname: this.userData.surname,
				phone: this.userData.phone,
				email: this.userData.email,
				date: this.selectedDate.toISOString().slice(0,10),
				service_id: service_ids,
				startTime: `${this.selectedTime}:00`,
				endTime: end_time,
			};
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
			console.log("Services Data Passed: " + JSON.stringify(this.selectedServices, null, 2));
		},

		// Show the CalendarUserData component and Submit button when the time is selected
		showCalendarUserData(time) {
			this.showCalendarUserDataComponent = true;
			this.selectedTime = time;
			console.log("Time: " + this.selectedTime);
		},

		// Store the user data emitted from the CalendarUserData component
		storeUserData(userData) {
			this.userData = userData;
		},
	},

	mounted() {
		console.log(window.yourPluginData);
	}
}
</script>
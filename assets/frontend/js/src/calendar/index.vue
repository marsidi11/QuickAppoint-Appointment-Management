<template>
	<div class="calendar-container">

		<CalendarHeader v-if="showCalendarHeaderComponent" :currentMonth="currentMonth" @reset-month="resetMonth" @prev-month="prevMonth" @next-month="nextMonth" />

		<CalendarBody v-if="showCalendarBodyComponent" :currentDate="currentDate" :daysOfWeek="daysOfWeek" :calendar="calendar" @date-selected="storeDate"  @next-month="nextMonth" @prev-month="prevMonth" @next-clicked="nextCalendarBody" />

		<CalendarServices v-if="showCalendarServicesComponent" :selected-date="selectedDate" @services-selected="storeServices" @prev-clicked="prevCalendarServices" @next-clicked="nextCalendarServices" />

		<CalendarTime v-if="showCalendarTimeComponent" :selected-services="selectedServices" @time-selected="storeTime" @prev-clicked="prevCalendarTime" @next-clicked="nextCalendarTime" />

		<CalendarUserData v-if="showCalendarUserDataComponent" :selected-time="selectedTime" @update-user-data="storeUserData" />

		<CreateAppointment v-if="showCalendarUserDataComponent" :appointment-data="appointmentData" @prev-clicked="prevCalendarUserData" />

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

			showCalendarHeaderComponent: true,
			showCalendarBodyComponent: true,
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

			const end_time = calculateEndTime(this.selectedTime, service_durations);

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
		resetMonth() {
			this.currentDate = new Date();
		},

		prevMonth() {
			this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
		},

		nextMonth() {
			this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
		},

		
		storeDate(date) { // Save the selected date from CalendarBody component
			this.selectedDate = date;
			console.log("Date Data Passed: " + this.selectedDate);
		},

		storeServices(services) { // Save the selected services from CalendarServices component
			this.selectedServices = services;
			console.log("Services Data Passed: " + JSON.stringify(this.selectedServices, null, 2));
		},

		storeTime(time) { // Save the selected time from CalendarTime component
			this.selectedTime = time;
			console.log("Time Data Passed: " + this.selectedTime);
		},

		storeUserData(userData) { // Store the user data emitted from the CalendarUserData component
			this.userData = userData;
		},

		nextCalendarBody() {
			this.showCalendarHeaderComponent = false;
			this.showCalendarBodyComponent = false;
			this.showCalendarServicesComponent = true;
		},

		prevCalendarServices() {
			this.showCalendarServicesComponent = false;
			this.showCalendarHeaderComponent = true;
			this.showCalendarBodyComponent = true;
		},

		nextCalendarServices() {
			this.showCalendarServicesComponent = false;
			this.showCalendarTimeComponent = true;
		},

		prevCalendarTime() {
			this.showCalendarTimeComponent = false;
			this.showCalendarServicesComponent = true;
		},

		nextCalendarTime() {
			this.showCalendarTimeComponent = false;
			this.showCalendarUserDataComponent = true;
		},

		prevCalendarUserData() {
			this.showCalendarUserDataComponent = false;
			this.showCalendarTimeComponent = true;
		},
		
	}
}
</script>
<template>
	<div class="calendar-container">
	<div class="calendar-section">
		
		<CalendarBody v-if="showCalendarBodyComponent" :currentMonth="currentMonth" :currentDate="currentDate" :daysOfWeek="daysOfWeek" :calendar="calendar" @date-selected="storeDate" @next-month="nextMonth" @prev-month="prevMonth" @reset-month="resetMonth" @next-clicked="nextCalendarBody" />

		<CalendarServices v-if="showCalendarServicesComponent" :selected-date="selectedDate" @selected-services="storeServices" @currency-symbol="storeCurrencySymbol" @prev-clicked="prevCalendarServices" @next-clicked="nextCalendarServices" />

		<CalendarTime v-if="showCalendarTimeComponent" :selected-date="selectedDate" :selected-services="selectedServices" @time-selected="storeTime" @prev-clicked="prevCalendarTime" @next-clicked="nextCalendarTime" />

		<CalendarUserData v-if="showCalendarUserDataComponent" :selected-time="selectedTime" :confirmation-data="confirmationData" :currency-symbol="currencySymbol" @update-user-data="storeUserData" />

		<CreateAppointment v-if="showCalendarUserDataComponent" :appointment-data="appointmentData" @prev-clicked="prevCalendarUserData" />
	
	</div>
	</div>

</template>

<script>
import { generateCalendar, calculateEndTime } from './CalendarUtils.js'; 
import CalendarBody from './CalendarBody.vue';
import CalendarServices from './CalendarServices.vue';
import CalendarTime from './CalendarTime.vue';
import CalendarUserData from './CalendarUserData.vue';
import CreateAppointment from './CreateAppointment.vue';


export default {
	name: 'CalendarComponent',

	components: {
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

			currencySymbol: null,

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
				date: `${this.selectedDate.getFullYear()}-${("0" + (this.selectedDate.getMonth() + 1)).slice(-2)}-${("0" + this.selectedDate.getDate()).slice(-2)}`,
				service_id: service_ids,
				startTime: `${this.selectedTime}:00`,
				endTime: end_time,
			};
		},

		confirmationData() {
			return {
				date: this.appointmentData.date,
				startTime: this.appointmentData.startTime,
				endTime: this.appointmentData.endTime,
				totalPrice: this.selectedServices.reduce((total, service) => total + Number(service.price), 0),
			};
		}
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

		storeCurrencySymbol(symbol) { // Save the currency symbol from CalendarServices component
			this.currencySymbol = symbol;
		},

		storeTime(time) { // Save the selected time from CalendarTime component
			this.selectedTime = time;
			console.log("Time Data Passed: " + this.selectedTime);
		},

		storeUserData(userData) { // Store the user data emitted from the CalendarUserData component
			this.userData = userData;
		},

		nextCalendarBody() {
			this.showCalendarBodyComponent = false;
			this.showCalendarServicesComponent = true;
		},

		prevCalendarServices() {
			this.showCalendarServicesComponent = false;
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
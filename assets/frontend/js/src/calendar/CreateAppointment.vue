<template>
    <button class="button-appointment" @click="createAppointment">Submit</button>
    <div v-if="loading" class="loading-message">Loading...</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
</template>

<script>
import apiService from './apiService.js';

export default {
    name: 'CreateAppointment',

    props: ['appointmentId', 'appointmentData'],

    data() {
        return {
            loading: false,
            errorMessage: null,
        };
    },

    methods: {
        // async getAppointment() {
        //     try {
        //         this.loading = true;
        //         const response = await apiService.getAppointment(this.appointmentId);
        //         // Update the component's data with the server's response
        //         // this.appointmentData = response;

        //         // Or emit an event with the server's response
        //         this.$emit('appointment-retrieved', response);
        //         console.log("Get Appointment: " + response);
        //     } catch (error) {
        //         this.errorMessage = error;
        //     } finally {
        //         this.loading = false;
        //     }
        // },

        async createAppointment() {
            try {
                this.$emit('submit');
                this.loading = true;
                console.log(this.appointmentData);
                const response = await apiService.createAppointment(this.appointmentData);
                // Handle the successful response, e.g., show a success message or update the component's data
                this.errorMessage = 'Appointment created successfully';
                console.log("Create Appointment: " + response);
            } catch (error) {
                this.errorMessage = error;
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
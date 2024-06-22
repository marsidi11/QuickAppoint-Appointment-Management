<template>
    <div class="calendar-nav">
        <button class="nav-previous" @click="$emit('prev-clicked')">Go Back</button>
        <button class="button-appointment" @click="createAppointment">Submit</button>
    </div>
    <div v-if="loading" class="loading-message">Loading...</div>
    <div v-if="messageInfo" :class="messageInfo.class">{{ messageInfo.message }}</div>
</template>

<script>
import { createAppointment } from './apiService.js';

export default {
    name: 'CreateAppointment',

    props: ['appointmentId', 'appointmentData'],

    data() {
        return {
            loading: false,
            errorMessage: null,
            successMessage: null,
        };
    },

    methods: {
        async createAppointment() {
            // Validate the required fields
            if (!this.appointmentData.name || !this.appointmentData.surname || !this.appointmentData.phone || !this.appointmentData.email) {
                this.errorMessage = 'All fields are required. Please complete the form.';
                return;
            }

            try {
                this.loading = true;
                this.errorMessage = null;
                this.successMessage = null;

                await createAppointment(this.appointmentData);

                this.successMessage = 'Appointment created successfully';

            } catch (error) {
                this.errorMessage = `Error creating appointment: ${error.message}`;

            } finally {
                this.loading = false;
            }
        },
    },

    computed: {
        messageInfo() {
            if (this.errorMessage) {
                return { message: this.errorMessage, class: 'error-message' };
            } else if (this.successMessage) {
                return { message: this.successMessage, class: 'success-message' };
            }
            return null;
        },
    },
};
</script>
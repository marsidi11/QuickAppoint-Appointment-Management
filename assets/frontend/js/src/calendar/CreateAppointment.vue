<template>
    <div class="calendar-nav">
        <button class="nav-previous" @click="$emit('prev-clicked')">Go Back</button>
        <button class="button-appointment" @click="createAppointment">Submit</button>
    </div>
    <div v-if="loading" class="loading-message">Loading...</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
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
        };
    },

    methods: {
        async createAppointment() {
            try {
                this.$emit('submit');
                this.loading = true;
                console.log(this.appointmentData);
                
                const response = await createAppointment(this.appointmentData);

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
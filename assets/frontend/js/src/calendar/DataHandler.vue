<template>
    <button class="button-appointment" @click="createBooking">Submit</button>
    <div v-if="loading" class="loading-message">Loading...</div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
</template>

<script>
import apiService from './apiService.js';

export default {
    name: 'DataHandler',

    props: ['bookingId', 'bookingData'],

    data() {
        return {
            loading: false,
            errorMessage: null,
        };
    },

    methods: {
        // async getBooking() {
        //     try {
        //         this.loading = true;
        //         const response = await apiService.getBooking(this.bookingId);
        //         // Update the component's data with the server's response
        //         // this.bookingData = response;

        //         // Or emit an event with the server's response
        //         this.$emit('booking-retrieved', response);
        //         console.log("Get Booking: " + response);
        //     } catch (error) {
        //         this.errorMessage = error;
        //     } finally {
        //         this.loading = false;
        //     }
        // },

        async createBooking() {
            try {
                this.$emit('submit');
                this.loading = true;
                console.log(this.bookingData);
                const response = await apiService.createBooking(this.bookingData);
                // Handle the successful response, e.g., show a success message or update the component's data
                this.errorMessage = 'Booking created successfully';
                console.log("Create Booking: " + response);
            } catch (error) {
                this.errorMessage = error;
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>
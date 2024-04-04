<template>
    
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'DataHandler',
    props: ['bookingId', 'bookingData'],
    methods: {
        getBooking() {
            axios.get(`/wp-json/booking-management/v1/bookings?id=${this.bookingId}`, {
                headers: {
                    'X-WP-Nonce': window.wpApiSettings.nonce
                }
            })
                .then(response => {
                    console.log('Booking data:', response.data);
                    // Update the component's data with the server's response
                    this.bookingData = response.data;

                    // Or emit an event with the server's response
                    this.$emit('booking-retrieved', response.data);
                })
                .catch(error => {
                    // Log the error for debugging
                    console.error('Error:', error);

                    // Check if the error response from server is available
                    if (error.response) {
                        // The request was made and the server responded with a status code
                        // that falls out of the range of 2xx
                        console.log(error.response.data);
                        console.log(error.response.status);
                        console.log(error.response.headers);

                        // You can also show a user-friendly error message
                        this.errorMessage = `Error: ${error.response.data.message || 'An error occurred.'}`;
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.log(error.request);
                        this.errorMessage = 'Error: No response from server.';
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.log('Error', error.message);
                        this.errorMessage = `Error: ${error.message}`;
                    }
                });
        },
        
        createBooking() {
            axios.post('/wp-json/booking-management/v1/bookings', this.bookingData, {
                headers: {
                    'X-WP-Nonce': window.wpApiSettings.nonce
                }
            })
            
                .then(response => {
                    // Update the component's data with the server's response
                    this.bookingData = response.data;

                    // Or emit an event with the server's response
                    this.$emit('booking-created', response.data);
                })

                .catch(error => {
                    // Log the error for debugging
                    console.error('Error:', error);

                    // Check if the error response from server is available
                    if (error.response) {
                        // The request was made and the server responded with a status code
                        // that falls out of the range of 2xx
                        console.log(error.response.data);
                        console.log(error.response.status);
                        console.log(error.response.headers);

                        // You can also show a user-friendly error message
                        this.errorMessage = `Error: ${error.response.data.message || 'An error occurred.'}`;
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.log(error.request);
                        this.errorMessage = 'Error: No response from server.';
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.log('Error', error.message);
                        this.errorMessage = `Error: ${error.message}`;
                    }
                });
        }
    }
}
</script>
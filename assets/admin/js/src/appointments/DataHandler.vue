<template>

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
        async getAllBookings() {
            try {
                this.loading = true;
                const response = await apiService.getAllBookings();
                console.log("Get All Bookings: ", JSON.stringify(response, null, 2));
                this.$emit('bookings-retrieved', response);

            } catch (error) {
                this.errorMessage = error;

            } finally {
                this.loading = false;
            }
        },


        async getBooking() {
            try {
                this.loading = true;
                const response = await apiService.getBooking(this.bookingId);

                this.$emit('booking-retrieved', response);
                console.log("Get Booking: " + response);

            } catch (error) {
                this.errorMessage = error;

            } finally {
                this.loading = false;
            }
        },

        async createBooking() {
            try {
                this.$emit('submit');
                this.loading = true;
                console.log(this.bookingData);

                const response = await apiService.createBooking(this.bookingData);
                this.errorMessage = 'Booking created successfully';
                console.log("Create Booking: " + response);

            } catch (error) {
                this.errorMessage = error;
                
            } finally {
                this.loading = false;
            }
        },
    },

    mounted() {
        this.getAllBookings();
    },
    
};
</script>
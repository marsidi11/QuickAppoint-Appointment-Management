<template>
    
    <button @click="loadMore" v-if="!loading">Load More</button>
    <div v-if="loading">Loading...</div>
    <div v-if="errorMessage">{{ errorMessage }}</div>

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
            page: 1, 
        };
    },

    methods: {
        async getAllBookings() {
            try {
                this.loading = true;
                const response = await apiService.getAllBookings(this.page);
                console.log("Get All Bookings: ", JSON.stringify(response, null, 2));
                this.$emit('updateBookings', response);

                if (response.length === 0) {
                    this.errorMessage = 'No more bookings to load';
                }

            } catch (error) {
                this.errorMessage = error;

            } finally {
                this.loading = false;
            }
        },

        loadMore() {
            this.page++;
            this.getAllBookings();
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
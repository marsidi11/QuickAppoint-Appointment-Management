<template>
    <!-- TODO: Style button and error message -->
    <button @click="loadMore" v-if="!loading" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 my-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Load More</button>
    <div v-if="loading">Loading...</div>
    <div v-if="errorMessage">{{ errorMessage }}</div>

</template>

<script>
import apiService from './apiService.js';

export default {
    name: 'GetAllAppointments',

    props: ['appointmentId', 'appointmentData'],

    data() {
        return {
            loading: false,
            errorMessage: null,
            page: 1, 
        };
    },

    methods: {
        async getAllAppointments() {
            try {
                this.loading = true;
                const response = await apiService.getAllAppointments(this.page);
                console.log("Get All Appointments: ", JSON.stringify(response, null, 2));
                this.$emit('updateAppointments', response);

                if (response.length === 0) {
                    this.errorMessage = 'No more appointments to load';
                }

            } catch (error) {
                this.errorMessage = error;

            } finally {
                this.loading = false;
            }
        },

        loadMore() {
            this.page++;
            this.getAllAppointments();
        },


        // async getAppointment() {
        //     try {
        //         this.loading = true;
        //         const response = await apiService.getAppointment(this.appointmentId);

        //         this.$emit('appointment-retrieved', response);
        //         console.log("Get Appointment: " + response);

        //     } catch (error) {
        //         this.errorMessage = error;

        //     } finally {
        //         this.loading = false;
        //     }
        // },

        // async createAppointment() {
        //     try {
        //         this.$emit('submit');
        //         this.loading = true;
        //         console.log(this.appointmentData);

        //         const response = await apiService.createAppointment(this.appointmentData);
        //         this.errorMessage = 'Appointment created successfully';
        //         console.log("Create Appointment: " + response);

        //     } catch (error) {
        //         this.errorMessage = error;
                
        //     } finally {
        //         this.loading = false;
        //     }
        // },
    },

    created() {
        this.getAllAppointments();
    },
    
};
</script>

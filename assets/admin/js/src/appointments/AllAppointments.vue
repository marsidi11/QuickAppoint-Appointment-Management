<template>

    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr>
            <th v-for="column in columns" :key="column" scope="col" class="px-6 py-3 uppercase">
              {{ column }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td class="px-6 py-4">{{ user.name + ' ' + user.surname }}</td>
            <td class="px-6 py-4">{{ user.phone }}</td>
            <td class="px-6 py-4">{{ user.email }}</td>
            <td class="px-6 py-4">{{ user.service_names }}</td>
            <td class="px-6 py-4">{{ user.date }}</td>
            <td class="px-6 py-4">{{ user.startTime }}</td>
            <td class="px-6 py-4">{{ user.endTime }}</td>
            <td class="px-6 py-4">{{ currencySymbol }}{{ user.total_price }}</td>
            <td class="px-6 py-4">{{ user.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- TODO: Style button and error message -->
    <button @click="loadMore" v-if="!loading" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 my-5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Load More</button>
    <div v-if="loading">Loading...</div>
    <div v-if="errorMessage">{{ errorMessage }}</div>

</template>

<script>
import { getAllAppointments, getCurrencySymbol } from './apiService.js';

export default {
    name: 'AllAppointments',

    props: ['appointmentData'],

    data() {
        return {
            columns: ['Name', 'Phone', 'Email', 'Services', 'Date', 'Start Time', 'End Time', 'Price', 'Status'],
			users: [],
            loading: false,
            errorMessage: null,
            page: 1, 
            currencySymbol: '$', // Default currency symbol
        };
    },

    methods: {
        async fetchAllAppointments() {
            try {
                this.loading = true;
                const response = await getAllAppointments(this.page);
                console.log("Get All Appointments: ", JSON.stringify(response, null, 2));
                this.users = [...this.users, ...response];

                if (response.length === 0) {
                    this.errorMessage = 'No more appointments to load';
                }

            } catch (error) {
                this.errorMessage = error;

            } finally {
                this.loading = false;
            }
        },

        // Get currency symbol
        async fetchCurrencySymbol() {
            try {
                const response = await getCurrencySymbol();
                console.log("Currency Symbol: ", JSON.stringify(response, null, 2));
                if (response) {
                    this.currencySymbol = response;
                }

            } catch (error) {
                this.errorMessage = error;
            } 
        },

        loadMore() {
            this.page++;
            this.fetchAllAppointments();
        },
    },

    created() {
        this.fetchAllAppointments();
        this.fetchCurrencySymbol();
    },
    
};
</script>

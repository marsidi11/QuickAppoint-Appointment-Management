<template>
    
    <DataHandler @bookings-retrieved="handleBookingsRetrieved" />

    <div class="relative overflow-x-auto">
      <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
          <tr :class="theadRowClass">
            <th v-for="column in columns" :key="column" scope="col" class="px-6 py-3">
              {{ column }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
            <td class="px-6 py-4">{{ user.name }}</td>
            <td class="px-6 py-4">{{ user.surname }}</td>
            <td class="px-6 py-4">{{ user.phone }}</td>
            <td class="px-6 py-4">{{ user.email }}</td>
            <td class="px-6 py-4">{{ user.services }}</td>
            <td class="px-6 py-4">{{ user.date }}</td>
            <td class="px-6 py-4">{{ user.startTime }}</td>
            <td class="px-6 py-4">{{ user.endTime }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </template>

<script>
import DataHandler from './DataHandler.vue';

export default {

    name: 'AppointmentsComponent',

    components: {
        DataHandler,
    },

    data() {
        return {
            columns: ['Name', 'Surname', 'Phone', 'Email', 'Services', 'Date', 'Start Time', 'End Time'],
            users: [],
        };
    },

    methods: {
        handleBookingsRetrieved(bookings) {
          this.users = bookings;
        },
    },
};
</script>
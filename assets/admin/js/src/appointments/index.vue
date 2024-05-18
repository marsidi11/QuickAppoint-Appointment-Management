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
            <!-- TODO: Add currency symbol from options in backend -->
            <td class="px-6 py-4">{{ user.total_price }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <GetAllAppointments @updateAppointments="handleUpdateAppointments" />

  </template>

<script>
import GetAllAppointments from './GetAllAppointments.vue';

export default {

	name: 'AppointmentsComponent',

	components: {
		GetAllAppointments,
	},

	data() {
		return {
			columns: ['Name', 'Phone', 'Email', 'Services', 'Date', 'Start Time', 'End Time', 'Price', 'Status'],
			users: [],
		};
	},

	methods: {
		handleUpdateAppointments(newAppointments) {
			this.users = [...this.users, ...newAppointments];
		},
	},
};
</script>
<template>
  <div class="overflow-x-auto sm:rounded-lg min-h-60">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
      <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
          <th v-for="column in columns" :key="column" scope="col" class="px-6 py-3 uppercase">
            {{ column }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="user in users" :key="user.id"
          class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
          <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
            {{ user.name + ' ' + user.surname }}
          </th>
          <td class="px-6 py-4">{{ user.phone }}</td>
          <td class="px-6 py-4">{{ user.email }}</td>
          <td class="px-6 py-4">{{ user.service_names }}</td>
          <td class="px-6 py-4">{{ user.date }}</td>
          <td class="px-6 py-4">{{ user.startTime }}</td>
          <td class="px-6 py-4">{{ user.endTime }}</td>
          <td class="px-6 py-4">{{ currencySymbol }}{{ user.total_price }}</td>
          <td class="px-6 py-4">
            <div class="relative">
              <select v-if="editingStatus[user.id]" v-model="user.status"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-all duration-300 ease-in-out">
                <option value="Confirmed">Confirmed</option>
                <option value="Pending">Pending</option>
                <option value="Cancelled">Cancelled</option>
              </select>
              <span v-else :class="{
                'px-2 py-1 rounded-full text-xs font-semibold': true,
                'bg-green-100 text-green-800': user.status === 'Confirmed',
                'bg-yellow-100 text-yellow-800': user.status === 'Pending',
                'bg-red-100 text-red-800': user.status === 'Cancelled'
              }">
                {{ user.status }}
              </span>
            </div>
          </td>
          <td class="px-6 py-4">
            <div class="flex items-center justify-end relative">
              <button v-if="editingStatus[user.id]" @click="saveStatus(user.id)"
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 transition-all duration-300 ease-in-out">
                Save
              </button>
              <button v-else @click="toggleDropdown(user.id)" :aria-expanded="dropdownStates[user.id] || false"
                :aria-controls="'userDropdown-' + user.id"
                class="inline-flex items-center p-2 text-sm font-medium text-center text-gray-900 bg-white rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-600 transition-all duration-300 ease-in-out">
                <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                  xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                </svg>
              </button>
              <div v-show="dropdownStates[user.id] && !editingStatus[user.id]" :id="'userDropdown-' + user.id"
                class="absolute right-0 z-10 bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700 dark:divide-gray-600">
                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownButton">
                  <li>
                    <button @click="startEditingStatus(user.id)"
                      class="block w-full px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white text-left transition-all duration-300 ease-in-out">
                      Update Status
                    </button>
                  </li>
                  <li>
                    <button @click="deleteAppointment(user.id)"
                      class="block w-full px-4 py-2 text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-red-500 dark:hover:text-red-400 text-left transition-all duration-300 ease-in-out">
                      Delete
                    </button>
                  </li>
                </ul>
              </div>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>


<script>
import { ref, reactive } from 'vue';
import { updateAppointmentStatus, deleteAppointment } from '../apiService.js';

export default {
  name: 'Table',

  props: ['columns', 'users', 'currencySymbol'],

  setup(props) {
    const dropdownStates = reactive({});
    const editingStatus = reactive({});

    const toggleDropdown = (userId) => {
      if (!editingStatus[userId]) {
        dropdownStates[userId] = !dropdownStates[userId];
      }
    };

    const closeDropdown = () => {
      Object.keys(dropdownStates).forEach(key => {
        dropdownStates[key] = false;
      });
    };

    const handleClickOutside = (event) => {
      const clickedInsideDropdown = Object.keys(dropdownStates).some(userId => {
        const dropdown = document.querySelector(`#userDropdown-${userId}`);
        const button = document.querySelector(`[aria-controls="userDropdown-${userId}"]`);
        if (button && dropdown) {
          return dropdown.contains(event.target) || button.contains(event.target);
        } else {
          return false;
        }
      });
      if (!clickedInsideDropdown) {
        closeDropdown();
      }
    };

    const deleteAppointment = async (appointmentId) => {
      try {
        await deleteAppointment(appointmentId);
      } catch (error) {
        console.error('Error deleting appointment:', error);
      }
    };

    const startEditingStatus = (userId) => {
      editingStatus[userId] = true;
      closeDropdown();
    };

    const saveStatus = async (userId) => {
      try {
        const user = props.users.find(u => u.id === userId);
        if (user) {
          await updateAppointmentStatus(userId, user.status);
          editingStatus[userId] = false;
        } else {
          console.error('User not found');
        }
      } catch (error) {
        console.error('Error updating status:', error);
      }
    };

    return {
      dropdownStates,
      editingStatus,
      toggleDropdown,
      closeDropdown,
      handleClickOutside,
      deleteAppointment,
      startEditingStatus,
      saveStatus
    };
  },

  mounted() {
    document.addEventListener('click', this.handleClickOutside);
  },

  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside);
  },
};
</script>

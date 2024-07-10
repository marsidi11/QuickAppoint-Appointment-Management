<template>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 min-h-36">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th v-for="column in columns" :key="column" scope="col" class="px-6 py-3 uppercase">
                        {{ column }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id" class="border-b dark:border-gray-700">
                    <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ user.name + ' ' + user.surname }}
                    </th>
                    <td class="px-4 py-3">{{ user.phone }}</td>
                    <td class="px-4 py-3">{{ user.email }}</td>
                    <td class="px-4 py-3">{{ user.service_names }}</td>
                    <td class="px-4 py-3">{{ user.date }}</td>
                    <td class="px-4 py-3">{{ user.startTime }}</td>
                    <td class="px-4 py-3">{{ user.endTime }}</td>
                    <td class="px-4 py-3">{{ currencySymbol }}{{ user.total_price }}</td>
                    <td class="px-4 py-3">{{ user.status }}</td>
                    <td class="px-4 py-3 flex items-center justify-end relative">
                        <button @click="toggleDropdown(user.id)" :aria-expanded="dropdownStates[user.id] || false" :aria-controls="'userDropdown-' + user.id"
                            class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                            type="button">
                            <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                            </svg>
                        </button>
                        <div v-show="dropdownStates[user.id]" :id="'userDropdown-' + user.id"
                            class="absolute right-0 z-10 bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600">
                            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownButton">
                                <li>
                                    <button @click="editAppointment(user.id)"
                                        class="block py-2 px-12 w-full hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                        Edit
                                </button>
                                </li>
                            </ul>
                            <div class="py-1">
                                <button @click="deleteAppointment(user.id)"
                                    class="block py-2 px-12 w-full text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">
                                    Delete
                            </button>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import { deleteAppointment } from '../apiService.js';

export default {
    name: 'Table',

    props: ['columns', 'users', 'currencySymbol'],

    data() {
        return {
            dropdownStates: {},
        };
    },
    methods: {
        toggleDropdown(userId) {
            this.dropdownStates = {
                [userId]: !this.dropdownStates[userId]
            };
        },

        closeDropdown() {
            this.dropdownStates = {};
        },
        
        handleClickOutside(event) {
             // Check if the click is outside all dropdowns and buttons
             const clickedInsideDropdown = Object.keys(this.dropdownStates).some(userId => {
                const dropdown = this.$el.querySelector(`#userDropdown-${userId}`);
                const button = this.$el.querySelector(`[aria-controls="userDropdown-${userId}"]`);
                if (button && dropdown) {
                    return dropdown.contains(event.target) || button.contains(event.target);
                } else {
                    return false;
                }
            });
            if (!clickedInsideDropdown) {
                this.closeDropdown();
            }
        },
        async deleteAppointment(appointmentId) {
            try {
                await deleteAppointment(appointmentId);
                this.$emit('appointment-deleted', appointmentId);

            } catch (error) {
                this.errorMessage = error;
            }
        },
    },
    mounted() {
        document.addEventListener('click', this.handleClickOutside);
    },
    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside);
    },
};
</script>
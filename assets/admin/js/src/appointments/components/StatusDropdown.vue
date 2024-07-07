<template>
    <div class="relative">
        <button @click="toggleDropdown" :aria-expanded="isOpen" aria-controls="statusDropdown"
            class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
            type="button">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-2 text-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Status
            <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path clip-rule="evenodd" fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
            </svg>
        </button>
        <div v-show="isOpen" id="statusDropdown"
            class="absolute right-0 z-10 mt-2 w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
            <ul class="space-y-2 text-sm" aria-labelledby="statusDropdownButton">
                <li v-for="statusOption in statusOptions" :key="statusOption.id" class="flex items-center">
                    <input :id="statusOption.id" type="checkbox" v-model="selectedStatuses" :value="statusOption.id"
                        class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label :for="statusOption.id" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{
                        statusOption.name }}</label>
                </li>
            </ul>
            <button @click="saveStatuses" class="mt-2 w-full py-2 px-4 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-700">
                Save
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'StatusDropdown',
    data() {
        return {
            isOpen: false,
            statusOptions: [
                { id: 'confirmed', name: 'Confirmed' },
                { id: 'pending', name: 'Pending' },
                { id: 'cancelled', name: 'Cancelled' },
            ],
            selectedStatuses: ['confirmed', 'pending', 'cancelled'],
        };
    },
    methods: {
        saveStatuses() {
            this.$emit('statuses-updated', this.selectedStatuses);
            this.closeDropdown();
        },
        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },
        closeDropdown() {
            this.isOpen = false;
        },
        handleClickOutside(event) {
            const dropdown = this.$el.querySelector('#statusDropdown');
            const button = this.$el.querySelector('button');
            if (dropdown && button && !dropdown.contains(event.target) && !button.contains(event.target)) {
                this.closeDropdown();
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
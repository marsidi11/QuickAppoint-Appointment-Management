<template>
    <div class="relative">
        <button @click="toggleDropdown" :aria-expanded="isOpen" aria-controls="filterDropdown"
            class="w-full md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700"
            type="button">
            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="h-4 w-4 mr-2 text-gray-400"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                    clip-rule="evenodd" />
            </svg>
            Filter
            <svg class="-mr-1 ml-1.5 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path clip-rule="evenodd" fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
            </svg>
        </button>
        <div v-show="isOpen" id="filterDropdown"
            class="absolute right-0 z-10 mt-2 w-48 p-3 bg-white rounded-lg shadow dark:bg-gray-700">
            <ul class="space-y-2 text-sm" aria-labelledby="filterDropdownButton">
                <li v-for="filterOption in filterOptions" :key="filterOption.id" class="flex items-center">
                    <input :id="filterOption.id" type="checkbox" v-model="selectedFilters" :value="filterOption.id"
                        class="w-4 h-4 bg-gray-100 border-gray-300 rounded text-primary-600 focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-700 focus:ring-2 dark:bg-gray-600 dark:border-gray-500">
                    <label :for="filterOption.id" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{
                        filterOption.name }}</label>
                </li>
            </ul>
            <button @click="saveFilters" class="mt-2 w-full py-2 px-4 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-4 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-700">
                Save
            </button>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FilterDropdown',
    data() {
        return {
            isOpen: false,
            filterOptions: [
                { id: 'upcoming', name: 'All Upcomings' },
                { id: 'today', name: 'Today' },
                { id: 'tomorrow', name: 'Tomorrow' },
                { id: 'lastMonth', name: 'Last Month' },
            ],
            selectedFilters: ['upcoming'],
        };
    },
    methods: {

        saveFilters() {
            this.$emit('filters-updated', this.selectedFilters);
            console.log("Selected Filters: " + this.selectedFilters);
            this.closeDropdown();
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
        },
        closeDropdown() {
            this.isOpen = false;
        },
        handleClickOutside(event) {
            const dropdown = this.$el.querySelector('#filterDropdown');
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

<template>
    <button type="button"
        @click="generateReport"
        class="flex items-center justify-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-[14px] px-4 py-2 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">
        <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true">
            <path clip-rule="evenodd" fill-rule="evenodd"
                d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
        </svg>
        Generate Report
    </button>
</template>

<script>
import { getAppointmentsReport } from '../apiService.js';

export default {
  name: 'AddProductButton',
  methods: {
    generateReport() {
      getAppointmentsReport().then(response => {
        const url = response.file_url;
        // Open the URL in a new tab
        window.open(url, '_blank');
      }).catch(error => {
        // Handle the error here
        console.error(error);
      });
    }
  }
};
</script>

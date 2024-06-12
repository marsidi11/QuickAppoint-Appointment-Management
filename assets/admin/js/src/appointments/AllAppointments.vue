<template>
  <section class="bg-gray-50 dark:bg-gray-900">
    <div class="mx-auto">

      <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
          <div class="w-full md:w-1/2">
            <SearchForm @searched-users="searchedUsers" />
          </div>
          <div
            class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
            <AddProductButton />
            <div class="flex items-center space-x-3 w-full md:w-auto">
              <ActionsDropdown />
              <FilterDropdown />
            </div>
          </div>
        </div>
        <Table :columns="columns" :users="users" :currencySymbol="currencySymbol" />

        <div class="pl-4">
          <button @click="loadMore" v-if="!loading"
            class="text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 my-5 dark:bg-primary-600 dark:hover:bg-primary-700 focus:outline-none dark:focus:ring-primary-800">Load
            More</button>
          <div v-if="loading">Loading...</div>
          <div v-if="errorMessage">{{ errorMessage }}</div>
        </div>
      </div>
    </div>
  </section>



</template>

<script>
import { getAllAppointments, getCurrencySymbol } from './apiService.js';
import SearchForm from './components/SearchForm.vue';
import AddProductButton from './components/AddProductButton.vue';
import ActionsDropdown from './components/ActionsDropdown.vue';
import FilterDropdown from './components/FilterDropdown.vue';
import Table from './components/Table.vue';

export default {
  name: 'AllAppointments',

  props: ['appointmentData'],

  data() {
    return {
      columns: ['Name', 'Phone', 'Email', 'Services', 'Date', 'Start Time', 'End Time', 'Price', 'Status', 'Action'],
      users: [],
      loading: false,
      errorMessage: null,
      page: 1,
      currencySymbol: '$', // Default currency symbol
      searchActive: false,
    };
  },

  components: {
    SearchForm,
    AddProductButton,
    ActionsDropdown,
    FilterDropdown,
    Table
  },

  methods: {

    searchedUsers(newUsers) {
      this.users = newUsers;
    },
    
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
        this.errorMessage = error.message || 'Failed to load appointments';
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
        this.errorMessage = error.message || 'Failed to fetch currency symbol';
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

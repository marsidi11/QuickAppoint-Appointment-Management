<template>
  <section class="bg-gray-50 dark:bg-gray-900">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0 md:space-x-4 p-6">
          <div class="w-full md:w-1/2">
            <SearchForm @search-updated="updateSearchQuery" />
          </div>
          <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
            <div class="flex items-center space-x-3 w-full md:w-auto">
              <FilterDropdown @filters-updated="updateDateFilter" />
              <StatusDropdown @statuses-updated="updateStatusFilter" />
              <ReportsButton />
            </div>
          </div>
        </div>

        <div v-if="errorMessage" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
          <p class="font-bold">Error</p>
          <p>{{ errorMessage }}</p>
        </div>

        <Table 
          :columns="columns" 
          :users="users" 
          :currencySymbol="currencySymbol" 
          @appointment-deleted="handleAppointmentDelete" 
        />

        <div class="p-6 bg-gray-50 dark:bg-gray-700">
          <div v-if="loading" class="flex justify-center items-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white"></div>
          </div>
          <Pagination 
            v-else
            :currentPage="currentPage" 
            :totalPages="totalPages" 
            :totalItems="totalItems"
            :itemsPerPage="itemsPerPage" 
            @page-changed="handlePageChange"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { getAllAppointments, getCurrencySymbol, getAppointmentsByFilter } from './apiService.js';
import SearchForm from './components/SearchForm.vue';
import FilterDropdown from './components/FilterDropdown.vue';
import StatusDropdown from './components/StatusDropdown.vue';
import ReportsButton from './components/ReportsButton.vue';
import Table from './components/Table.vue';
import Pagination from './components/Pagination.vue';

export default {
  name: 'AllAppointments',

  props: ['appointmentData'],

  data() {
    return {
      columns: ['Name', 'Phone', 'Email', 'Services', 'Date', 'Start Time', 'End Time', 'Price', 'Status', 'Action'],
      users: [],
      loading: false,
      errorMessage: null,
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      itemsPerPage: 10,
      currencySymbol: '$', // Default currency symbol
      searchActive: false,
      searchQuery: '',
      dateFilters: ['upcoming'],
      statusFilters: ['confirmed', 'pending', 'cancelled'],
    };
  },

  components: {
    SearchForm,
    FilterDropdown,
    StatusDropdown,
    ReportsButton,
    Table,
    Pagination,
  },

  methods: {

    // Get currency symbol
    async fetchCurrencySymbol() {
      try {
        const response = await getCurrencySymbol();
        if (response) {
          this.currencySymbol = response;
        }
      } catch (error) {
        this.errorMessage = error.message || 'Failed to fetch currency symbol';
      }
    },

    // Fetch all appointments on page load
    async fetchAllAppointments() {
      try {
        this.loading = true;
        const response = await getAllAppointments(this.currentPage, this.itemsPerPage);

        if (response.message) {
            this.users = [];
            this.totalItems = 0;
            this.totalPages = 0;
            this.errorMessage = response.message;
        } else {
            this.users = response.data;
            this.totalItems = response.total;
            this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
            this.errorMessage = ''; // Clear any previous error message
        }

      } catch (error) {
        this.errorMessage = error.message || 'Failed to load appointments';
      } finally {
        this.loading = false;
      }
    },

    // Fetch appointments by search query and filter
    async fetchAppointmentsByFilter() {
      try {
        this.loading = true;
        const response = await getAppointmentsByFilter(this.searchQuery, this.currentPage, this.itemsPerPage, this.dateFilters, this.statusFilters);

        if (response.message) {
            this.users = [];
            this.totalItems = 0;
            this.totalPages = 0;
            this.errorMessage = response.message;
        } else {
            this.users = response.data;
            this.totalItems = response.total;
            this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
            this.errorMessage = ''; // Clear any previous error message
        }

      } catch (error) {
        this.errorMessage = error.message || 'Failed to load appointments';
      } finally {
        this.loading = false;
      }
    },

    handlePageChange(newPage) {
      this.currentPage = newPage;
      if (this.searchActive) {
        this.fetchAppointmentsByFilter();
      } else {
        this.fetchAllAppointments();
      }
    },

    updateSearchQuery(query) {
      this.searchQuery = query;
      this.page = 1;
      this.searchActive = true;
      this.fetchAppointmentsByFilter();
    },

    updateDateFilter(filters) {
      this.dateFilters = filters;
      this.page = 1;
      this.searchActive = true;
      this.fetchAppointmentsByFilter();
    },

    updateStatusFilter(status) {
      this.statusFilters = status;
      this.page = 1;
      this.searchActive = true;
      this.fetchAppointmentsByFilter();
    },

    emptySearch() {
      this.searchActive = false;
      this.page = 1;
      this.users = [];
      this.fetchAllAppointments();
    },

    handleAppointmentDelete(appointmentId) {
      this.users = this.users.filter(user => user.id !== appointmentId); // Remove the deleted appointment from the list locally
    },

    // Load more appointments
    loadMore() {
      this.page++;

      if (!this.searchActive) {
        this.fetchAllAppointments();
      } else {
        this.fetchAppointmentsByFilter();
      }
    },
  },

  created() {
    this.fetchAllAppointments();
    this.fetchCurrencySymbol();
  },

};
</script>
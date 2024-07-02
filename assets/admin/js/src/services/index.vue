<template>
  <div class="relative overflow-x-auto">

    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">

      <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
        <tr>
          <th v-for="column in columns" :key="column" scope="col" class="px-6 py-3 uppercase">
            {{ column }}
          </th>
          <th scope="col" class="px-6 py-3 uppercase">
            Action
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="service in services" :key="service.id"
          class="bg-white border-b dark:bg-gray-800 dark:border-gray-70">

          <td class="px-6 py-4 font-medium text-gray-800">
            <template v-if="editingServiceId === service.id">
              <input type="text" v-model="service.name" />
            </template>
            <template v-else>
              {{ service.name }}
            </template>
          </td>
          <td class="px-6 py-4 text-gray-800">
            <template v-if="editingServiceId === service.id">
              <input type="text" v-model="service.description" />
            </template>
            <template v-else>
              {{ service.description }}
            </template>
          </td>
          <td class="px-6 py-4 text-gray-800">
            <template v-if="editingServiceId === service.id">
              <input type="number" v-model.number="service.duration" min="0" step="1" />
            </template>
            <template v-else>
              {{ service.duration }} minutes
            </template>
          </td>
          <td class="px-6 py-4 text-gray-800">
            <template v-if="editingServiceId === service.id">
              <input type="number" v-model.number="service.price" />
            </template>
            <template v-else>
              {{ currencySymbol }}{{ service.price }}
            </template>
          </td>

          <td class="px-6 py-4 text-sm font-medium">
            <UpdateService :serviceData="service" :serviceId="service.id" :editingServiceId="editingServiceId"
            @edit-active="handleEditActive(service.id)" @service-updated="handleServiceUpdated" />

            <DeleteService :serviceData="service" :serviceId="service.id" @service-deleted="handleServiceUpdated" />
          </td>

        </tr>

        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-70">
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="text" v-model="serviceData.name" placeholder="Enter service name">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="text" v-model="serviceData.description" placeholder="Short description">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="number" v-model.number="serviceData.duration" placeholder="Duration (minutes)">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="number" v-model.number="serviceData.price" placeholder="Service price">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <CreateService :serviceData="serviceData" @create-service="handleCreateService" />
          </td>
        </tr>

      </tbody>

    </table>

  </div>

  <GetServices ref="getServicesRef" @get-services="handleGetServices" />


</template>

<script>
import { getCurrencySymbol } from './apiService.js';

import CreateService from './CreateService.vue';
import DeleteService from './DeleteService.vue';
import UpdateService from './UpdateService.vue';
import GetServices from './GetServices.vue';

export default {
  name: 'ServicesComponent',

  components: {
    CreateService,
    DeleteService,
    UpdateService,
    GetServices,
  },

  data() {
    return {
      columns: ['Name', 'Description', 'Duration', 'Price'],

      serviceData: {
        name: '',
        description: '',
        duration: null,
        price: null
      },

      services: [],
      currencySymbol: '$', // Default currency symbol

      editingServiceId: null, // Track the ID of the service being edited
    }
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
        this.errorMessage = error;
      }
    },

    async handleCreateService() {

      // Reset form values
      this.serviceData = {
        name: '',
        description: '',
        duration: '',
        price: ''
      };

      this.handleServiceUpdated();

    },

    async handleGetServices(services) {
      this.services = services;
    },

    async handleServiceUpdated() {
      this.$refs.getServicesRef.getServices();
      this.editingServiceId = null;
    },

    handleEditActive(serviceId) {
      this.editingServiceId = serviceId;
    },
  },

  created() {
    this.fetchCurrencySymbol();
  },

};
</script>
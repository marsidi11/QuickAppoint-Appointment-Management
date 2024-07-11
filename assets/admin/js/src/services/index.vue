<template>
  <div class="p-6 bg-white rounded-lg shadow-lg">
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700">
        <thead class="text-xs uppercase bg-gray-100">
          <tr>
            <th v-for="column in columns" :key="column" scope="col" class="px-6 py-4 font-semibold tracking-wider">
              {{ column }}
            </th>
            <th scope="col" class="px-6 py-4 font-semibold tracking-wider">
              Action
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="service in services" :key="service.id"
            class="border-b border-gray-200 hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 font-medium">
              <template v-if="editingServiceId === service.id">
                <input type="text" v-model="service.name" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ service.name }}
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingServiceId === service.id">
                <input type="text" v-model="service.description" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ service.description }}
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingServiceId === service.id">
                <input type="number" v-model.number="service.duration" min="0" step="1" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ service.duration }} minutes
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingServiceId === service.id">
                <input type="number" v-model.number="service.price" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ currencySymbol }}{{ service.price }}
              </template>
            </td>
            <td class="px-6 py-4">
              <div class="flex space-x-2">
                <UpdateService 
                  :serviceData="service" 
                  :serviceId="service.id" 
                  :editingServiceId="editingServiceId"
                  @edit-active="handleEditActive(service.id)" 
                  @service-updated="handleServiceUpdated" 
                  class="text-blue-600 hover:text-blue-800"
                />
                <DeleteService 
                  :serviceData="service" 
                  :serviceId="service.id" 
                  @service-deleted="handleServiceUpdated" 
                  class="text-red-600 hover:text-red-800"
                />
              </div>
            </td>
          </tr>
          <tr class="border-b border-gray-200 bg-gray-50">
            <td class="px-6 py-4">
              <input type="text" v-model="serviceData.name" placeholder="Enter service name" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <input type="text" v-model="serviceData.description" placeholder="Short description" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <input type="number" v-model.number="serviceData.duration" placeholder="Duration (minutes)" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <input type="number" v-model.number="serviceData.price" placeholder="Service price" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <CreateService 
                :serviceData="serviceData" 
                @create-service="handleCreateService" 
                class="px-4 py-2 text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
              />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <GetServices ref="getServicesRef" @get-services="handleGetServices" />
  </div>
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
      this.$refs.getServicesRef.fetchServices();
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
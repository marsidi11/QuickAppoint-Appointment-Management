<template>
  <div class="p-6 bg-white rounded-lg">
    <div class="overflow-x-auto">
      <table class="w-full text-[14px] text-left text-gray-700">
        <thead class="text-[12px] uppercase bg-gray-100">
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
                <input type="text" v-model="service.name"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ service.name }}
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingServiceId === service.id">
                <input type="text" v-model="service.description"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ service.description }}
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingServiceId === service.id">
                <input type="number" v-model.number="service.duration" min="0" step="1"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ service.duration }} minutes
              </template>
            </td>
            <td class="px-6 py-4">
              <template v-if="editingServiceId === service.id">
                <input type="number" v-model.number="service.price"
                  class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" />
              </template>
              <template v-else>
                {{ currencySymbol }}{{ service.price }}
              </template>
            </td>
            <td class="px-6 py-4">
              <div class="flex space-x-2">
                <UpdateService :serviceData="service" :serviceId="service.id" :editingServiceId="editingServiceId"
                  @edit-active="handleEditActive(service.id)" @service-updated="handleServiceUpdated" />
                <DeleteService :serviceData="service" :serviceId="service.id" @service-deleted="handleServiceUpdated" />
              </div>
            </td>
          </tr>
          <tr class="border-b border-gray-200 bg-gray-50">
            <td class="px-6 py-4">
              <input type="text" v-model="newServiceData.name" placeholder="Enter service name"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <input type="text" v-model="newServiceData.description" placeholder="Short description"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <input type="number" v-model.number="newServiceData.duration" placeholder="Duration (minutes)"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <input type="number" v-model.number="newServiceData.price" placeholder="Service price"
                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </td>
            <td class="px-6 py-4">
              <CreateService :serviceData="newServiceData" @service-created="handleServiceCreated" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <GetServices ref="getServicesRef" @get-services="handleGetServices" />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
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

  setup() {
    const columns = ref(['Name', 'Description', 'Duration', 'Price']);
    const newServiceData = ref({
      name: '',
      description: '',
      duration: null,
      price: null
    });
    const services = ref([]);
    const currencySymbol = ref('$');
    const editingServiceId = ref(null);
    const getServicesRef = ref(null);

    const fetchCurrencySymbol = async () => {
      try {
        const response = await getCurrencySymbol();
        if (response) {
          currencySymbol.value = response;
        }
      } catch (error) {
        console.error('Error fetching currency symbol:', error);
      }
    };

    const handleServiceCreated = () => {
      // Reset the form
      newServiceData.value = {
        name: '',
        description: '',
        duration: null,
        price: null
      };
      // Refresh the services list
      getServicesRef.value.fetchServices();
    };

    const handleGetServices = (fetchedServices) => {
      services.value = fetchedServices;
    };

    const handleServiceUpdated = () => {
      getServicesRef.value.fetchServices();
      editingServiceId.value = null;
    };

    const handleEditActive = (serviceId) => {
      editingServiceId.value = serviceId;
    };

    onMounted(() => {
      fetchCurrencySymbol();
    });

    return {
      columns,
      newServiceData,
      services,
      currencySymbol,
      editingServiceId,
      getServicesRef,
      handleServiceCreated,
      handleGetServices,
      handleServiceUpdated,
      handleEditActive,
    };
  },
};
</script>
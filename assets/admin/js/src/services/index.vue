<template>
  <!-- TODO: Make some fields required -->
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

          <td class="px-6 py-4 font-medium text-gray-800">{{ service.name }}</td>
          <td class="px-6 py-4 text-gray-800">{{ service.description }}</td>
          <td class="px-6 py-4 text-gray-800">{{ service.duration }} minutes</td>
          <td class="px-6 py-4 text-gray-800">{{ service.price }}</td>

          <td class="px-6 py-4 text-sm font-medium">
            <DeleteService :serviceData="service" :serviceId="service.id" @serviceDeleted="handleServiceDeleted" />
          </td>

        </tr>

        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-70">
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="text" v-model="serviceData.name" placeholder="Enter service name">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="text" v-model="serviceData.description" placeholder="Enter service description">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="text" v-model="serviceData.duration" placeholder="Enter service duration (minutes)">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <input type="text" v-model="serviceData.price" placeholder="Enter service price">
          </td>
          <td class="px-6 py-4 font-medium text-gray-800">
            <CreateService :serviceData="serviceData" @createService="handleCreateService" />
          </td>
        </tr>

      </tbody>

    </table>

  </div>

  <GetServices ref="getServicesRef" @getServices="handleGetServices" />


</template>

<script>
import CreateService from './CreateService.vue';
import DeleteService from './DeleteService.vue';
import GetServices from './GetServices.vue';

export default {
    name: 'ServicesComponent',

    components: {
      CreateService,
      DeleteService,
      GetServices,
    },
    
  data() {
    return {
      columns: ['Name', 'Description', 'Duration', 'Price'],

      serviceData: {
        name: '',
        description: '',
        duration: '',
        price: ''
      },

      services: [],
    }
  },

  methods: {

    async handleCreateService() {

      // Create a new service object
      // TODO: Accept only minutes in duration
      // TODO: Accept only numbers in price
      const newService = {
        name: this.serviceData.name,
        description: this.serviceData.description,
        duration: this.serviceData.duration,
        price: this.serviceData.price
      };

      this.services.push(newService);

      // Reset form values
      this.serviceData = {
        name: '',
        description: '',
        duration: '',
        price: ''
      };

    },

    async handleGetServices(services) {
      this.services = services;
    },

    async handleServiceDeleted() {
      this.$refs.getServicesRef.getServices();
    },
  },
}
</script>
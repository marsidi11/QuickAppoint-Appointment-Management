<template>
  <div class="flex flex-col">
    <div class="-m-1.5 overflow-x-auto">
      <div class="p-1.5 min-w-full inline-block align-middle">
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th v-for="column in columns" :key="column" scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">
                  {{ column }}
                </th>
                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">
                  Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="service in services" :key="service.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800">
                  {{ service.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                  {{ service.description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                  {{ service.duration }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">
                  {{ service.price }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                  <button type="button"
                    class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none">Delete</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <GetServices @getServices="handleGetServices" />

  <div class="form-inputs">
    <input type="text" v-model="serviceData.name" placeholder="Enter service name">
    <input type="text" v-model="serviceData.description" placeholder="Enter service description">
    <input type="text" v-model="serviceData.duration" placeholder="Enter service duration">
    <input type="text" v-model="serviceData.price" placeholder="Enter service price">

    <CreateService :serviceData="serviceData" @createService="handleCreateService" />
  </div>

</template>

<script>
import CreateService from './CreateService.vue';
import GetServices from './GetServices.vue';

export default {
    name: 'ServicesComponent',

    components: {
      CreateService,
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
  },
}
</script>
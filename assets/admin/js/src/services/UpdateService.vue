<template>
    <button 
      v-if="serviceId != editingServiceId" 
      @click="editService"
      class="inline-flex items-center gap-x-2 px-3 py-2 text-[14px] font-semibold rounded-lg border border-blue-600 text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
    >
      Edit
    </button>
  
    <button 
      v-else 
      @click="saveService"
      :disabled="isLoading"
      class="inline-flex items-center gap-x-2 px-3 py-2 text-[14px] font-semibold rounded-lg border border-blue-600 text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ isLoading ? 'Saving...' : 'Save' }}
    </button>
  
    <div v-if="errorMessage" class="mt-2 text-red-600">{{ errorMessage }}</div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import { updateService } from './apiService.js';
  
  export default {
    name: 'UpdateService',
    props: ['serviceId', 'serviceData', 'editingServiceId'],
    emits: ['edit-active', 'service-updated'],
    setup(props, { emit }) {
      const errorMessage = ref(null);
      const isLoading = ref(false);
  
      const editService = () => {
        emit('edit-active');
      };
  
      const saveService = async () => {
        try {
          isLoading.value = true;
          await updateService(props.serviceId, props.serviceData);
          emit('service-updated');
        } catch (error) {
          errorMessage.value = error.message;
        } finally {
          isLoading.value = false;
        }
      };
  
      return { editService, saveService, errorMessage, isLoading };
    }
  }
  </script>
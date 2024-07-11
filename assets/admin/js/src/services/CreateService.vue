<template>
    <button 
      @click="handleCreateService" 
      :disabled="isLoading"
      class="px-4 py-2 font-semibold text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ isLoading ? 'Creating...' : 'Create Service' }}
    </button>
    <div v-if="errorMessage" class="mt-2 text-red-600">{{ errorMessage }}</div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import { createService } from './apiService.js';
  
  export default {
    name: 'CreateService',
    props: ['serviceData'],
    emits: ['service-created'],
    setup(props, { emit }) {
      const errorMessage = ref(null);
      const isLoading = ref(false);
  
      const handleCreateService = async () => {
        try {
          isLoading.value = true;
          errorMessage.value = null;
          await createService(props.serviceData);
          emit('service-created');
        } catch (error) {
          errorMessage.value = error.message;
        } finally {
          isLoading.value = false;
        }
      };
  
      return { handleCreateService, errorMessage, isLoading };
    }
  }
  </script>
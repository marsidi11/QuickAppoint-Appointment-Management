<template>
    <button 
      @click="confirmDelete" 
      :disabled="isLoading"
      class="inline-flex items-center gap-x-2 px-3 py-2 text-[14px] font-semibold rounded-lg border border-red-600 text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {{ isLoading ? 'Deleting...' : 'Delete' }}
    </button>
    <div v-if="errorMessage" class="mt-2 text-red-600">{{ errorMessage }}</div>
  </template>
  
  <script>
  import { ref } from 'vue';
  import { deleteService } from './apiService.js';
  
  export default {
    name: 'DeleteService',
    props: ['serviceId', 'serviceData'],
    emits: ['service-deleted'],
    setup(props, { emit }) {
      const errorMessage = ref(null);
      const isLoading = ref(false);
  
      const confirmDelete = async () => {
        if (confirm('Are you sure you want to delete this service?')) {
          try {
            isLoading.value = true;
            await deleteService(props.serviceId);
            emit('service-deleted');
          } catch (error) {
            errorMessage.value = error.message;
          } finally {
            isLoading.value = false;
          }
        }
      };
  
      return { confirmDelete, errorMessage, isLoading };
    }
  }
</script>
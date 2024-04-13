<template>

    <button @click="deleteService()" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none">
        Delete
    </button>
    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import apiService from './apiService.js';

export default {
    name: 'DeleteService',

    props: ['serviceId', 'serviceData'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        async deleteService() {
            try {
                const response = await apiService.deleteService(this.serviceId);
                console.log("Deleted Service: ", this.serviceId);
                this.$emit('serviceDeleted');

            } catch (error) {
                this.errorMessage = error;
            }
        },

    },

}

</script>
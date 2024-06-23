<template>

    <button v-if="serviceId != editingServiceId" @click="editService()"
        class="inline-flex items-center gap-x-2 mr-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none">
        Edit
    </button>

    <button v-if="serviceId == editingServiceId" @click="saveService()"
        class="inline-flex items-center gap-x-2 mr-2 text-sm font-semibold rounded-lg border border-transparent text-blue-600 hover:text-blue-800 disabled:opacity-50 disabled:pointer-events-none">
        Save
    </button>

    <div v-if="errorMessage">{{ errorMessage }}</div>

</template>

<script>
import { updateService } from './apiService.js';

export default {
    name: 'UpdateService',

    props: ['serviceId', 'serviceData', 'editingServiceId'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        editService() {
            this.$emit('edit-active');
        },

        async saveService() {
            try {
                await updateService(this.serviceId, this.serviceData);
                this.$emit('service-updated');

            } catch (error) {
                this.errorMessage = error;
            }
        },
    },

}

</script>
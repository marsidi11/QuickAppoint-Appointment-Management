<template>

    <button @click="deleteService()" class="inline-flex items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent text-red-600 hover:text-red-800 disabled:opacity-50 disabled:pointer-events-none">
        Delete
    </button>
    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import { deleteService } from './apiService.js';

// TODO: What if service is deleted by mistake? How to recover it? What will show for past appointments?
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
                const response = await deleteService(this.serviceId);
                console.log("Deleted Service: ", this.serviceId);
                console.log("Delete Service: ", JSON.stringify(response, null, 2));
                this.$emit('serviceDeleted');

            } catch (error) {
                this.errorMessage = error;
            }
        },

    },

}

</script>
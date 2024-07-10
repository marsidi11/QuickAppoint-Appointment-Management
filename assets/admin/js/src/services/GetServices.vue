<template>

    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import { getServices } from './apiService.js';

export default {
    
    name: 'GetService',

    props: ['serviceData'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        async fetchServices() {
            try {
                this.errorMessage = null;
                const response = await getServices();
                this.$emit('get-services', response.data);

            } catch (error) {
                this.errorMessage = error.message || 'An error occurred while fetching services';
            }
        },
        
    },

    created() {
        this.fetchServices();
    },
}

</script>
<template>

    <button @click="createService">Create Service</button>
    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import apiService from './apiService.js';

export default {

    name: 'CreateService',

    props: ['serviceData'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        async createService() {
            try {
                const response = await apiService.createService(this.serviceData);
                this.$emit('createService', response);
                console.log("Created Service: ", JSON.stringify(response, null, 2));
                this.errorMessage = "Service created successfully"

            } catch (error) {
                this.errorMessage = error;
            }
        },

    },

}

</script>
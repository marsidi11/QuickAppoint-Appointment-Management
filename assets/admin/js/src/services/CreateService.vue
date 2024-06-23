<template>

    <button @click="createService">Create Service</button>
    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import { createService } from './apiService.js';

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
                await createService(this.serviceData);
                this.$emit('create-service');

            } catch (error) {
                this.errorMessage = error;
            } 
        },

    },

}

</script>
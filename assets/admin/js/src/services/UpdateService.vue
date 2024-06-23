<template>

    <button>Update</button>
    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import apiService from './apiService.js';

// TODO: Implement the update service method

export default {
    name: 'UpdateService',

    props: ['serviceData'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        async updateService(id, data) {
            try {
                await apiService.updateService(id, data);
                this.getServices();

            } catch (error) {
                this.errorMessage = error;
            }
        }, 
    },

}

</script>
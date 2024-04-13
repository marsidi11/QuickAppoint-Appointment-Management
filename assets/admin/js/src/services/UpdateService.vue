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
                const response = await apiService.updateService(id, data);
                console.log("Update Service: ", JSON.stringify(response, null, 2));
                this.getServices();

            } catch (error) {
                this.errorMessage = error;
            }
        }, 
    },

}

</script>
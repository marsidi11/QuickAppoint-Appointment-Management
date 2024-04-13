<template>

    <button>Delete</button>
    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import apiService from './apiService.js';

export default {
    name: 'DeleteService',

    props: ['serviceData'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        async deleteService(id) {
            try {
                const response = await apiService.deleteService(id);
                console.log("Delete Service: ", JSON.stringify(response, null, 2));
                this.getServices();

            } catch (error) {
                this.errorMessage = error;
            }
        },

    },

}

</script>
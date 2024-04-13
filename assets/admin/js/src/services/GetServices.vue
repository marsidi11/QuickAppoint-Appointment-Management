<template>

    <div v-if="errorMessage">{{ errorMessage }}</div>
    
</template>

<script>
import apiService from './apiService.js';

export default {
    name: 'GetService',

    props: ['serviceData'],

    data() {
        return {
            errorMessage: null,
        };
    },

    methods: {

        async getServices() {
            try {
                const response = await apiService.getServices();
                console.log("Get All Services: ", JSON.stringify(response, null, 2));
                this.$emit('getServices', response);

            } catch (error) {
                this.errorMessage = error;
            }
        },
        
    },

    created() {
        this.getServices();
    },
}

</script>
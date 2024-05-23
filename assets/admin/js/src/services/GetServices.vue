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

        async getServices() {
            try {
                const response = await getServices();
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
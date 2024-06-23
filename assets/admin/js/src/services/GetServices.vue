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
                this.$emit('get-services', response);

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
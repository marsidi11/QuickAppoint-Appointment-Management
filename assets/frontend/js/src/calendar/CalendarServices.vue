<template>
    <div class="calendar-services">

        <h2 class="calendar-services-header">Select a Service:</h2>

        <div v-for="service in services" :key="service.id" @click="selectService(service)">
            <div class="service-box" :class="{ 'selected-service': selectedServices.some(selectedService => selectedService.id === service.id) }">
                <h3>{{ service.name }}</h3>
                <p>{{ service.description }}</p>
                <p>{{ service.duration }} minutes</p>
                <p>${{ service.price }}</p>
            </div>
        </div>

        <div v-if="errorMessage">{{ errorMessage }}</div>
        
    </div>

    <div class="calendar-nav">
        <button class="nav-previous">Go Back</button>
        <button class="nav-next">Next</button>
    </div>

</template>

<script>
import { getServices } from './apiService.js';

export default {
    name: 'CalendarServices',

    data() {
        return {
            services: [],
            selectedServices: [],
            errorMessage: null,
        };
    },

    methods: {

        // Display services on frontend
        async fetchServices() {
            try {
                const response = await getServices();
                console.log("Get All Services: ", JSON.stringify(response, null, 2));
                this.services = response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get selected services
        selectService(service) {
            const index = this.selectedServices.findIndex(selectedService => selectedService.id === service.id);
            
            if (index > -1) {
                this.selectedServices.splice(index, 1); // Remove the service ID from the array if it's already selected
            } else {
                this.selectedServices.push({ 
                    id: service.id, 
                    duration: service.duration 
                }); // Add the service ID and Duration to the array if it's not already selected
            }
            this.$emit('servicesSelected', this.selectedServices); // Emit the array of selected service IDs
        },
        
    },

    // TODO: Call fetchServices() method when index.vue is created
    // Call fetchServices() method when component is created
    created() {
        this.fetchServices();
    },
};
</script>
<template>
    <div class="calendar-services">

        <h2 class="calendar-services-header">Select a Service</h2>

        <div v-if="loading" class="loading-style-1"></div>

        <div :class="['services-list', gridColumnsClass]">

            <div v-for="service in services" :key="service.id" @click="selectService(service)">
                <div class="service-box"
                    :class="{ 'selected-service': selectedServices.some(selectedService => selectedService.id === service.id) }">
                    <h3>{{ service.name }}</h3>
                    <p>{{ service.description }}</p>
                    <p>{{ service.duration }} minutes</p>
                    <p>${{ service.price }}</p>
                </div>
            </div>
        </div>

    </div>

    <div class="calendar-nav">
        <button class="nav-previous" @click="$emit('prev-clicked')">Go Back</button>
        <button class="nav-next" @click="nextClicked">Next</button>
    </div>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>

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
            loading: false,
        };
    },

    computed: {
        gridColumnsClass() {
            switch (this.services.length) {
                case 1:
                    return 'grid-cols-1';
                case 2:
                    return 'grid-cols-2';
                case 3:
                    return 'grid-cols-2 md:grid-cols-3';
                default:
                    return 'grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5';
            }
        }
    },

    methods: {

        // Display services on frontend
        async fetchServices() {
            try {
                this.loading = true;
                const response = await getServices();
                console.log("Get All Services: ", JSON.stringify(response, null, 2));
                this.services = response;

            } catch (error) {
                this.errorMessage = error;
            } finally {
                this.loading = false;
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
            this.$emit('services-selected', this.selectedServices); // Emit the array of selected service IDs
        },

        nextClicked() {
            if (this.selectedServices.length > 0) {
                this.$emit('next-clicked');
            } else {
                this.errorMessage = 'Please select at least one service';
            }
        },
        
    },

    // TODO: Call fetchServices() method when index.vue is created
    // Call fetchServices() method when component is created
    created() {
        this.fetchServices();
    },
};
</script>
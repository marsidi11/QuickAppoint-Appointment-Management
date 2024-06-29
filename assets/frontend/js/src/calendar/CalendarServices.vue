<template>
    <div class="calendar-services">

        <h2 class="calendar-services-header">Select a Service</h2>

        <div v-if="loading" class="loading-style-1"></div>

        <div :class="['services-list', gridColumnsClass]">

            <div class="services-container" v-for="service in services" :key="service.id" @click="selectService(service)">
                <div class="service-box"
                    :class="{ 'selected-service': selectedServices.some(selectedService => selectedService.id === service.id) }">
                    <h3 class="service-name">{{ service.name }}</h3>
                    <p class="service-description">{{ service.description }}</p>
                    <p class="service-duration">{{ service.duration }} minutes</p>
                    <p class="service-price">{{ currencySymbol }}{{ service.price }}</p>
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
import { getServices, getCurrencySymbol } from './apiService.js';

export default {
    name: 'CalendarServices',

    data() {
        return {
            services: [],
            selectedServices: [],
            errorMessage: null,
            loading: false,
            currencySymbol: '$', // Default currency symbol
        };
    },

    computed: {
        gridColumnsClass() {
            const classes = [
                'grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
                'grid-cols-1',
                'grid-cols-2',
                'grid-cols-2 md:grid-cols-3',
                'grid-cols-2 md:grid-cols-3 lg:grid-cols-2',
                'grid-cols-2 md:grid-cols-3 lg:grid-cols-3',
            ];
            return classes[Math.min(this.services.length, 5)];
        },
    },

    methods: {

        // Get services on frontend
        async fetchServices() {
            try {
                this.loading = true;
                const response = await getServices();
                this.services = response;

            } catch (error) {
                this.errorMessage = error;
            }
        },

        // Get currency symbol
        async fetchCurrencySymbol() {
            try {
                this.loading = true;
                const response = await getCurrencySymbol();
                if (response) {
                    this.currencySymbol = response;
                }
                this.$emit('currency-symbol', this.currencySymbol);

            } catch (error) {
                this.errorMessage = error;
            } finally {
                this.loading = false;
            }
        },

        isSelected(service) {
            return this.selectedServices.some(selectedService => selectedService.id === service.id);
        },

        // Save selected services
        selectService(service) {
            const index = this.selectedServices.findIndex(selectedService => selectedService.id === service.id);
            if (index > -1) {
                this.selectedServices.splice(index, 1); // Remove the service if already selected
            } else {
                this.selectedServices.push(service); // Add the service if not selected
            }
            this.$emit('selected-services', this.selectedServices); // Emit the selected services
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
        this.fetchCurrencySymbol();
    },
};
</script>
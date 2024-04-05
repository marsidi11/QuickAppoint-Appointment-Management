<template>
    <div class="calendar-services">

        <h2 class="calendar-services-header">Select a Service:</h2>

        <div v-for="service in services" :key="service.id" @click="selectService(service)">
            <div class="service-box">
                <h3>{{ service.name }}</h3>
                <p>{{ service.description }}</p>
            </div>
        </div>
        
    </div>
</template>

<script>
export default {
    name: 'CalendarServices',

    data() {
        return {
            services: [
                { id: 1, name: 'Service 1', description: 'Description of Service 1' },
                { id: 2, name: 'Service 2', description: 'Description of Service 2' },
                { id: 3, name: 'Service 3', description: 'Description of Service 3' },
            ],
            selectedService: null,
        };
    },

    methods: {
        selectService(service) {
            this.selectedService = service;
            this.$emit('services-selected', service.name);
        },
    },
};
</script>
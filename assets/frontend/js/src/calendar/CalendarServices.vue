<template>
    <div>
        <h2>Select a Service:</h2>
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
            // You can save the selected service data or perform any other actions here
        },
    },
};
</script>

<style scoped>
.service-box {
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
    cursor: pointer;
}
</style>
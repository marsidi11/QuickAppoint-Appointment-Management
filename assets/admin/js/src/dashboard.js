import { createApp, h } from 'vue';
import AppointmentsComponent from './appointments/index.vue';
import ServicesComponent from './services/index.vue';

document.addEventListener('DOMContentLoaded', () => {

    createApp({
        render: () => h(AppointmentsComponent)
    }).mount('#all-appointments-am');

    createApp({
        render: () => h(ServicesComponent)
    }).mount('#services-am');

});
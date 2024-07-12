import { createApp, h } from 'vue';
import AppointmentsComponent from './appointments/index.vue';
import ServicesComponent from './services/index.vue';

document.addEventListener('DOMContentLoaded', () => {

    createApp({
        render: () => h(AppointmentsComponent)
    }).mount('#all-appointments-quickappoint');

    createApp({
        render: () => h(ServicesComponent)
    }).mount('#services-quickappoint');

});
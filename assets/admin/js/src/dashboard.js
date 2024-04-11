import { createApp, h } from 'vue';
import AppointmentsComponent from './appointments/index.vue';

document.addEventListener('DOMContentLoaded', () => {
    createApp({
        render: () => h(AppointmentsComponent)
    }).mount('#tab-1');
});
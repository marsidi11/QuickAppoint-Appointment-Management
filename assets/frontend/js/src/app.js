import { createApp, h } from 'vue';
import CalendarComponent from './calendar/index.vue';

document.addEventListener('DOMContentLoaded', () => {
    createApp({
        render: () => h(CalendarComponent)
    }).mount('#app');
});
import { createApp } from 'vue';
import CalendarComponent from './calendar/index.vue';

createApp({
    components: {
        'calendar-component': CalendarComponent
    }
}).mount('#app');
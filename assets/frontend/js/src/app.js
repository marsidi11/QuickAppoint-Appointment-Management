import { createApp } from 'vue';
import CalendarComponent from './calendar/index.vue';

console.log('app.js is being called'); // Add this line

createApp({
    components: {
        'calendar-component': CalendarComponent
    }
}).mount('#app');
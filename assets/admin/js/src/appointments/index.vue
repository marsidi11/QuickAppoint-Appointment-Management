<template>

    <AllAppointments @updateAppointments="handleUpdateAppointments" />

</template>

<script>
import AllAppointments from './AllAppointments.vue';

export default {

	name: 'AppointmentsComponent',

	components: {
		AllAppointments,
	},

};
</script>
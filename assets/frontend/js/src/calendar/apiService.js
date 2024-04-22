import axios from 'axios';

// Helper function to handle errors
function handleError(error) {
	console.error('Error:', error);

	if (error.response) {
		console.log(error.response.data);
		console.log(error.response.status);
		console.log(error.response.headers);
		return `Error: ${error.response.data.message || 'An error occurred.'}`;
	} else if (error.request) {
		console.log(error.request);
		return 'Error: No response from server.';
	} else {
		console.log('Error', error.message);
		return `Error: ${error.message}`;
	}
}

// API service module
const apiService = {

	// Create Appointment
	async createAppointment(appointmentData) {

		if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
			console.log('Nonce is not set');
			return;
		}

		try {
			const response = await axios.post(window.wpApiSettings.apiUrlAppointments + '/create', appointmentData, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
			});
			return response.data;
		} catch (error) {
			throw handleError(error);
		}
	},

	// Get Services
	async getServices() { 

		if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
			console.log('Nonce is not set');
			return;
		}

		try {
			const response = await axios.get(window.wpApiSettings.apiUrlServices, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
			});
			return response.data;

		} catch (error) {
			throw handleError(error);
		}
	},
};

export default apiService;
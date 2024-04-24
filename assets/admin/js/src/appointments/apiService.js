import axios from 'axios';

// Function to handle errors
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

// Check if API Settings are set
function checkApiSettings() {
	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return false;
	}
	return true;
}

const apiService = {
	
    async getAllAppointments(page) { 

        if (!checkApiSettings()) return;

        try {
			const response = await axios.get(`${window.wpApiSettings.apiUrlAppointments}?page=${page}`, {
                headers: {
                    'X-WP-Nonce': window.wpApiSettings.nonce,
                },
            });
            return response.data;

        } catch (error) {
			throw handleError(error);
		}
    },

	async getAppointment(appointmentId) {

		if (!checkApiSettings()) return;

		try {
			const response = await axios.get(`/wp-json/appointment_management/v1/appointments?id=${appointmentId}`, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
			});
			return response.data;
			
		} catch (error) {
			throw handleError(error);
		}
	},

	async createAppointment(appointmentData) {

		if (!checkApiSettings()) return;

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
};

export default apiService;
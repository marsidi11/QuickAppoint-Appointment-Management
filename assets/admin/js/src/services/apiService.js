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

// Check if API Settings are set
function checkApiSettings() {
	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return false;
	}
	return true;
}

// API service module
const apiService = {

	async createService(serviceData) {

		if (!checkApiSettings()) return;

		try {
			const response = await axios.post(window.wpApiSettings.apiUrlServices + '/create', serviceData, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
			});
			return response.data;
		} catch (error) {
			throw handleError(error);
		}
	},

	async getServices() { 

        if (!checkApiSettings()) return;

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

	async deleteService(serviceId) {

		if (!checkApiSettings()) return;

		try {
			const response = await axios.delete(window.wpApiSettings.apiUrlServices + '/delete/' + serviceId, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
			});
			return response.data;
		} catch (error) {
			throw handleError(error);
		}
	}

};

export default apiService;
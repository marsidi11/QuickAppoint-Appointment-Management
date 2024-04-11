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

const apiService = {
    async getAllBookings(page) { 

        if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
			console.log('Nonce is not set');
			return;
		}

        try {
			const response = await axios.get(`${window.wpApiSettings.apiUrl}?page=${page}`, {
                headers: {
                    'X-WP-Nonce': window.wpApiSettings.nonce,
                },
            });
            return response.data;

        } catch (error) {
			throw handleError(error);
		}
    },

	async getBooking(bookingId) {

		if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
			console.log('Nonce is not set');
			return;
		}

		try {
			const response = await axios.get(`/wp-json/booking-management/v1/bookings?id=${bookingId}`, {
				headers: {
					'X-WP-Nonce': window.wpApiSettings.nonce,
				},
			});
			return response.data;
			
		} catch (error) {
			throw handleError(error);
		}
	},

	async createBooking(bookingData) {

		if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
			console.log('Nonce is not set');
			return;
		}
        // TODO : Check for better way to get the api url
		try {
			const response = await axios.post(window.wpApiSettings.apiUrl + '/create', bookingData, {
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
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
	async getBooking(bookingId) {
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
		try {
			const response = await axios.post('/wordpress-test/wp-json/booking-management/v1/bookings/create', bookingData, {
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
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

// Helper function to get headers
function getHeaders() {
    return {
        'X-WP-Nonce': window.wpApiSettings.nonce,
    };
}

// Helper function to make GET requests
async function apiGet(url, params = {}) {
    if (!checkApiSettings()) return;

    try {
        const response = await axios.get(url, {
            headers: getHeaders(),
            params,
        });
        return response.data;
    } catch (error) {
        throw handleError(error);
    }
}

// Helper function to make POST requests
// async function apiPost(url, data) {
//     if (!checkApiSettings()) return;

//     try {
//         const response = await axios.post(url, data, {
//             headers: getHeaders(),
//         });
//         return response.data;
//     } catch (error) {
//         throw handleError(error);
//     }
// }

/**
 * Get All Appointments
 * @param {Object} appointmentData - Page number for pagination
 * @returns {Promise<Object>}
 */
export async function getAllAppointments(page) {
    return apiGet(`${window.wpApiSettings.apiUrlAppointments}`, { page });
}

/**
 * Get Appointment By Search & Filter Date
 * @param {Object} appointmentData - Search query, filter date and page number for pagination
 * @returns {Promise<Object>}
 */
export async function getAppointmentsBySearch(searchTerm = '', page = 1, dateFilters = []) {
    const params = { page };

    if (searchTerm) {
        params.search = searchTerm;
    }

    if (dateFilters.length > 0) {
        params.dateFilters = dateFilters;
    }
    const queryParams = new URLSearchParams(params).toString();
    console.log(`${window.wpApiSettings.apiUrlAppointments}/search?${queryParams}`);
    return apiGet(`${window.wpApiSettings.apiUrlAppointments}/search?${queryParams}`);
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/currency-symbol');
}
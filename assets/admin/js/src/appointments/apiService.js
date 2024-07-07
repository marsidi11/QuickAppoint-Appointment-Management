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

// Helper function to make DELETE requests
async function apiDelete(url, params = {}) {
    if (!checkApiSettings()) return;

    try {
        const response = await axios.delete(url, {
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
 * Delete Appointment
 * @param {Object} appointmentId - Appointment Id to delete
 * @returns {Promise<Object>}
 */
export async function deleteAppointment(appointmentId) {
	return apiDelete(window.wpApiSettings.apiUrlAppointments + '/delete/' + appointmentId);
}

/**
 * Get Appointment By Search & Filter Date
 * @param {Object} appointmentData - Search query, filter date and page number for pagination
 * @returns {Promise<Object>}
 */
export async function getAppointmentsByFilter(searchTerm = '', page = 1, dateFilters = [], statusFilters = []) {
    const params = { page };

    if (searchTerm) {
        params.search = searchTerm;
    }

    if (dateFilters.length > 0) {
        params.dateFilters = dateFilters;
    }

    if (statusFilters.length > 0) {
        params.statusFilters = statusFilters;
    }
    
    const queryParams = new URLSearchParams(params).toString();
    return apiGet(`${window.wpApiSettings.apiUrlAppointments}/search?${queryParams}`);
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/currency-symbol');
}

/**
 * Get Appointments Data Report
 * @param {Object} startDate & endDate - Dates range for report
 * @returns {Promise<Object>}
 */
export async function getAppointmentsReport() {
    return apiGet(window.wpApiSettings.apiUrlAppointments + '/get-data-report');
}
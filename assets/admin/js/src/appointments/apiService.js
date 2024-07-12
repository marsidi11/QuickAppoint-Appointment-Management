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
	if (!window.am_plugin_api_settings || !window.am_plugin_api_settings.nonce) {
		console.log('Nonce is not set');
		return false;
	}
	return true;
}

// Helper function to get headers
function getHeaders() {
    return {
        'X-WP-Nonce': window.am_plugin_api_settings.nonce,
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

// Helper function to make UPDATE requests
async function apiUpdate(url, data) {
    if (!checkApiSettings()) return;

    try {
        const response = await axios.put(url, data, {
            headers: getHeaders(),
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
 * @param {number} page - Page number for pagination
 * @param {number} itemsPerPage - Number of items per page
 * @returns {Promise<Object>}
 */
export async function getAllAppointments(page = 1, itemsPerPage = 10) {
    const params = { page, per_page: itemsPerPage };
    const queryParams = new URLSearchParams(params).toString();
    const response = await apiGet(`${window.am_plugin_api_settings.apiUrlAppointments}?${queryParams}`);

    if (response.message) {
        return {
            data: [],
            total: 0,
            totalPages: 0,
            message: response.message
        };
    }
    
    return {
        data: response.appointments,
        total: response.total,
        totalPages: response.total_pages
    };
}

/**
 * Update Appointment
 * @param {Object} appointmentId - Appointment Id to update
 * @param {Object} status - New status of the appointment
 * @returns {Promise<Object>}
 */
export async function updateAppointmentStatus(appointmentId, status) {
    return apiUpdate(window.am_plugin_api_settings.apiUrlAppointments + '/update/' + appointmentId, { status: status });
}

/**
 * Delete Appointment
 * @param {Object} appointmentId - Appointment Id to delete
 * @returns {Promise<Object>}
 */
export async function deleteAppointment(appointmentId) {
	return apiDelete(window.am_plugin_api_settings.apiUrlAppointments + '/delete/' + appointmentId);
}

/**
* Get Appointment By Search & Filter Date
* @param {string} searchTerm - Search query
* @param {number} page - Page number for pagination
* @param {number} itemsPerPage - Number of items per page
* @param {string[]} dateFilters - Array of date filters
* @param {string[]} statusFilters - Array of status filters
* @returns {Promise<Object>}
*/
export async function getAppointmentsByFilter(searchTerm = '', page = 1, itemsPerPage = 10, dateFilters = [], statusFilters = []) {
   const params = { page, per_page: itemsPerPage };

   if (searchTerm) {
       params.search = searchTerm;
   }

   if (dateFilters.length > 0) {
       params.dateFilters = dateFilters.join(',');
   }

   if (statusFilters.length > 0) {
       params.statusFilters = statusFilters.join(',');
   }
   
   const queryParams = new URLSearchParams(params).toString();
   const response = await apiGet(`${window.am_plugin_api_settings.apiUrlAppointments}/search?${queryParams}`);

   if (response.message) {
    return {
        data: [],
        total: 0,
        totalPages: 0,
        message: response.message
    };
}
   
   return {
       data: response.appointments,
       total: response.total,
       totalPages: response.total_pages
   };
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.am_plugin_api_settings.apiUrlOptions + '/currency-symbol');
}

/**
 * Get Appointments Data Report
 * @param {Object} startDate & endDate - Dates range for report
 * @returns {Promise<Object>}
 */
export async function getAppointmentsReport() {
    return apiGet(window.am_plugin_api_settings.apiUrlAppointments + '/get-data-report');
}
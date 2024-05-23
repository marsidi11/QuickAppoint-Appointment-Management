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
async function apiPost(url, data) {
    if (!checkApiSettings()) return;

    try {
        const response = await axios.post(url, data, {
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

/**
 * Create New Service
 * @param {Object} serviceData - Service data
 * @returns {Promise<Object>}
 */
export async function createService(serviceData) {
	return apiPost(window.wpApiSettings.apiUrlServices + '/create', serviceData);
}

/**
 * Get Services
 * @returns {Promise<Object>}
 */
export async function getServices() {
	return apiGet(window.wpApiSettings.apiUrlServices);
}

/**
 * Delete Service
 * @param {Object} appointmentData - The appointment data
 * @returns {Promise<Object>}
 */
export async function deleteService(serviceId) {
	return apiDelete(window.wpApiSettings.apiUrlServices + '/delete/' + serviceId);
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/currency-symbol');
}
import axios from 'axios';

// Helper function to handle errors
function handleError(error) {
    if (error.response && error.response.data && error.response.data.error) {
        return new Error(error.response.data.error);
    } else if (error.message) {
        return new Error(error.message);
    } else {
        return new Error('An unknown error occurred');
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
            params: params,
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

/**
 * Create New Service
 * @param {Object} serviceData - Service data
 * @returns {Promise<Object>}
 */
export async function createService(serviceData) {
	return apiPost(window.am_plugin_api_settings.apiUrlServices + '/create', serviceData);
}

/**
 * Get Services
 * @returns {Promise<Object>}
 */
export async function getServices() {
	return apiGet(window.am_plugin_api_settings.apiUrlServices);
}

/**
 * Delete Service
 * @param {Object} serviceId - The service id
 * @returns {Promise<Object>}
 */
export async function deleteService(serviceId) {
	return apiDelete(window.am_plugin_api_settings.apiUrlServices + '/delete/' + serviceId);
}

/**
 * Update Service
 * @param {Object} serviceId - The service id
 * @returns {Promise<Object>}
 */
export async function updateService(serviceId, data) {
	return apiUpdate(window.am_plugin_api_settings.apiUrlServices + '/update/' + serviceId, data);
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.am_plugin_api_settings.apiUrlOptions + '/currency-symbol');
}
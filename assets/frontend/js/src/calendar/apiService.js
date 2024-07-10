/*
* API Service Module
*/
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

/**
 * Create Appointment
 * @param {Object} appointmentData - The appointment data
 * @returns {Promise<Object>}
 */
export async function createAppointment(appointmentData) {
	try {
        const response = await apiPost(window.am_plugin_api_settings.apiUrlAppointments + '/create', appointmentData);
        
        return {
            success: true,
            message: response.message,
            confirmationUrl: response.confirmation_url
        };
    } catch (error) {
        console.error('Error creating appointment:', error);
        return {
            success: false,
            message: error.message || 'An unexpected error occurred. Please try again later.'
        };
    }
}

/**
 * Get Services
 * @returns {Promise<Object>}
 */
export async function getServices() {
	return apiGet(window.am_plugin_api_settings.apiUrlServices);
}

/**
 * Get Dates Range To Allow Bookings
 * @returns {Promise<Object>}
 */
export async function getDatesRange() {
	return apiGet(window.am_plugin_api_settings.apiUrlOptions + '/dates-range');
}

/**
 * Get Open Days
 * @returns {Promise<Object>}
 */
export async function getOpenDays() {
	return apiGet(window.am_plugin_api_settings.apiUrlOptions + '/open-days');
}

/**
 * Get Reserved Time Slots
 * @param {string} date @param {int} duration - The date to get reserved time slots for and duration to calculate available time slots
 * @returns {Promise<Object>}
 */
export async function getAvailableTimeSlots(date, serviceDuration) {
	return apiGet(window.am_plugin_api_settings.apiUrlAppointments + '/available-time-slots', { date, serviceDuration });
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.am_plugin_api_settings.apiUrlOptions + '/currency-symbol');
}
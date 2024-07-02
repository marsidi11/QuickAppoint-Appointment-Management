/*
* API Service Module
*/
import axios from 'axios';

// Helper function to handle errors
function handleError(error) {
    console.error('Error:', error);

    if (error.response) {
        console.log(error.response.data);
        console.log(error.response.status);
        console.log(error.response.headers);
        throw new Error(error.response.data.message || 'An error occurred.');
    } else if (error.request) {
        console.log(error.request);
        throw new Error('No response from server.');
    } else {
        console.log('Error', error.message);
        throw new Error(error.message);
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
	return apiPost(window.wpApiSettings.apiUrlAppointments + '/create', appointmentData);
}

/**
 * Get Services
 * @returns {Promise<Object>}
 */
export async function getServices() {
	return apiGet(window.wpApiSettings.apiUrlServices);
}

/**
 * Get Dates Range To Allow Bookings
 * @returns {Promise<Object>}
 */
export async function getDatesRange() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/dates-range');
}

/**
 * Get Open Days
 * @returns {Promise<Object>}
 */
export async function getOpenDays() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/open-days');
}

/**
 * Get Reserved Time Slots
 * @param {string} date @param {int} duration - The date to get reserved time slots for and duration to calculate available time slots
 * @returns {Promise<Object>}
 */
export async function getAvailableTimeSlots(date, serviceDuration) {
	return apiGet(window.wpApiSettings.apiUrlAppointments + '/available-time-slots', { date, serviceDuration });
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/currency-symbol');
}
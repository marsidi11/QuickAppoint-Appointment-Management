/*
* API Service Module
*/
import axios from 'axios';

// Helper function to handle errors
// TODO: Add more error handling for all apiService functions
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
 * Get Open Time
 * @returns {Promise<Object>}
 */
export async function getOpenTime() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/open-time');
}

/**
 * Get Close Time
 * @returns {Promise<Object>}
 */
export async function getCloseTime() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/close-time');
}

/**
 * Get Time Slot Duration
 * @returns {Promise<Object>}
 */
export async function getTimeSlotDuration() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/time-slot-duration');
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
 * Get Break Start Time
 * @returns {Promise<Object>}
 */
export async function getBreakStart() {
	const response = await apiGet(window.wpApiSettings.apiUrlOptions + '/break-start');
	return response.code === 'no_value' ? null : response;
}

/**
 * Get Break End Time
 * @returns {Promise<Object>}
 */
export async function getBreakEnd() {
	const response = await apiGet(window.wpApiSettings.apiUrlOptions + '/break-end');
	return response.code === 'no_value' ? null : response;
}

/**
 * Get Reserved Time Slots
 * @param {string} date - The date to get reserved time slots for
 * @returns {Promise<Object>}
 */
export async function getReservedTimeSlots(date) {
	return apiGet(window.wpApiSettings.apiUrlAppointments + '/reserved-time-slots', { date });
}

/**
 * Get Currency Symbol
 * @returns {Promise<Object>}
 */
export async function getCurrencySymbol() {
	return apiGet(window.wpApiSettings.apiUrlOptions + '/currency-symbol');
}
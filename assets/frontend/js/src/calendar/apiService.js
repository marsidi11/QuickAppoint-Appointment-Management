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
		return `Error: ${error.response.data.message || 'An error occurred.'}`;
	} else if (error.request) {
		console.log(error.request);
		return 'Error: No response from server.';
	} else {
		console.log('Error', error.message);
		return `Error: ${error.message}`;
	}
}

// Create Appointment
export async function createAppointment(appointmentData) {

	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return;
	}

	try {
		const response = await axios.post(window.wpApiSettings.apiUrlAppointments + '/create', appointmentData, {
			headers: {
				'X-WP-Nonce': window.wpApiSettings.nonce,
			},
		});
		return response.data;
	} catch (error) {
		throw handleError(error);
	}
}

// Get Services
export async function getServices() {

	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return;
	}

	try {
		const response = await axios.get(window.wpApiSettings.apiUrlServices, {
			headers: {
				'X-WP-Nonce': window.wpApiSettings.nonce,
			},
		});
		return response.data;

	} catch (error) {
		throw handleError(error);
	}
}

// Get Open Time
export async function getOpenTime() {

	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return;
	}

	try {
		const response = await axios.get(window.wpApiSettings.apiUrlOptions + '/open-time', {
			headers: {
				'X-WP-Nonce': window.wpApiSettings.nonce,
			},
		});
		return response.data;

	} catch (error) {
		throw handleError(error);
	}
}

// Get Close Time
export async function getCloseTime() {

	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return;
	}

	try {
		const response = await axios.get(window.wpApiSettings.apiUrlOptions + '/close-time', {
			headers: {
				'X-WP-Nonce': window.wpApiSettings.nonce,
			},
		});
		return response.data;

	} catch (error) {
		throw handleError(error);
	}
}

// Get Dates Range To Allow Bookings
export async function getDatesRange() {

	if (!window.wpApiSettings || !window.wpApiSettings.nonce) {
		console.log('Nonce is not set');
		return;
	}

	try {
		const response = await axios.get(window.wpApiSettings.apiUrlOptions + '/dates-range', {
			headers: {
				'X-WP-Nonce': window.wpApiSettings.nonce,
			},
		});
		return response.data;

	} catch (error) {
		throw handleError(error);
	}
}

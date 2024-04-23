import moment from 'moment';

/**
 * CalendarUtils.js
 * Functions for index.vue
 */

// Generate Calendar
export function generateCalendar(currentDate) {
    const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const days = [];
    const firstDayOfMonth = (date.getDay() + 6) % 7;

    // Add days from the previous month to the current calendar
    for (let i = firstDayOfMonth; i > 0; i--) {
        const prevMonthDay = new Date(date);
        prevMonthDay.setDate(prevMonthDay.getDate() - i);
        days.unshift({ date: prevMonthDay });
    }

    // Add days of the current month
    while (date.getMonth() === currentDate.getMonth()) {
        days.push({ date: new Date(date) });
        date.setDate(date.getDate() + 1);
    }

    // Calculate the number of days to add from the next month
    const lastDayOfMonth = days[days.length - 1].date.getDay();
    let daysToAdd = 0;
    
    // If the last day of the month is not Sunday, add days from the next month
    if (lastDayOfMonth !== 0) {
        daysToAdd = 7 - lastDayOfMonth;
    }

    // Add days from the next month to the current calendar
    for (let i = 1; i <= daysToAdd; i++) {
        const nextMonthDay = new Date(days[days.length - 1].date);
        nextMonthDay.setDate(nextMonthDay.getDate() + 1);
        days.push({ date: nextMonthDay });
    }

    // Group days into weeks
    const weeks = [];
    for (let i = 0; i < days.length; i += 7) {
        weeks.push(days.slice(i, i + 7));
    }

    return weeks;
}


// Check if the date is the current day
export function isCurrentDay(date) {
    const today = new Date();
    return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    );
}


// Check if a date is before today
export function isPastDate(date) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return new Date(date).setHours(0, 0, 0, 0) < today;
}


// Check if the date is within the next x days from today
export function isDateWithinNextXDays(date, x) {
    const today = new Date();
    today.setHours(0, 0, 0, 0); // Set the time to 00:00:00.000

    const maxDate = new Date(today.getFullYear(), today.getMonth(), today.getDate() + x);

    date = new Date(date);
    date.setHours(0, 0, 0, 0); // Set the time to 00:00:00.000

    return date >= today && date <= maxDate;
}


// When the users clicks a date
export function dayClicked(date) {
    console.log(date);
    let numberOfDays = 14;
    
    // Check if the date is within the allowed range (today to the next number of days)
    if (isDateWithinNextXDays(date, numberOfDays)) {
        this.selectedDate = date;
        // Optionally, you can emit an event or call a method to communicate with the PHP backend
        this.$emit('dateSelected', date);
    }
}


export function isDateWithinAllowedRange(date) {
    // Check if the date is within the allowed range (today to the next 14 days)
    return isDateWithinNextXDays(date, 14);
}


// Calculate Duration of Appointment
export function calculateEndTime(startTime, serviceDurations) {
    // Create a moment object for the start time
    const startMoment = moment(startTime, 'HH:mm');

    // Calculate the total duration in minutes
    const totalDurationMinutes = serviceDurations.reduce((sum, duration) => sum + Number(duration), 0);

    // Create a new moment object for the end time
    const endMoment = moment(startMoment).add(totalDurationMinutes, 'minutes');

    // Format the end time in 24-hour format
    const formattedEndTime = endMoment.format('HH:mm:ss');

    return formattedEndTime;
}
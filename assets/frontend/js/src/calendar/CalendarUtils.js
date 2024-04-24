import moment from 'moment';

/**
 * CalendarUtils.js
 * Functions for index.vue
 */

// Generate Calendar
export function generateCalendar(currentDate) {
    const date = moment(currentDate).startOf('month');
    const days = [];
    const firstDayOfMonth = (date.day() + 6) % 7;

    // Add days from the previous month to the current calendar
    for (let i = 0; i < firstDayOfMonth; i++) {
        days.unshift({ date: moment(date).subtract(i + 1, 'days').toDate() });
    }

    // Add days of the current month
    while (date.month() === currentDate.getMonth()) {
        days.push({ date: moment(date).toDate() });
        date.add(1, 'days');
    }

    // Calculate the number of days to add from the next month
    const lastDayOfMonth = moment(days[days.length - 1].date);
    let daysToAdd = 0;
    if (lastDayOfMonth.day() !== 0) { // If the last day of the month is not a Sunday
        daysToAdd = 7 - lastDayOfMonth.day();
    }

    // Add days from the next month to the current calendar
    for (let i = 1; i <= daysToAdd; i++) {
        days.push({ date: lastDayOfMonth.clone().add(i, 'days').toDate() });
    }

    // Group days into weeks
    const weeks = [];
    for (let i = 0; i < days.length; i += 7) {
        weeks.push(days.slice(i, i + 7));
    }

    return weeks;
}

export function isCurrentDay(date) {
    return moment().isSame(date, 'day');
}

export function isPastDate(date) {
    return moment(date).isBefore(moment(), 'day');
}


// Check if the date is within the next x days from today
export function isDateWithinNextXDays(date, x) {
    const today = moment().startOf('day');
    const maxDate = moment(today).add(x, 'days');
    date = moment(date).startOf('day');
    return date.isSameOrAfter(today) && date.isSameOrBefore(maxDate);
}


// Check if the date is within the allowed range (today to the next 14 days)
export function isDateWithinAllowedRange(date, x) {
    return isDateWithinNextXDays(date, x);
}

// Check if the day is set open by admin
export function isOpenDay(currentDate, openDays) {
    const currentDay = moment(currentDate).format('dddd');
    const lowercaseDaysArray = openDays.map(day => day.toLowerCase());
    return lowercaseDaysArray.includes(currentDay.toLowerCase());
}

// Calculate End Time (Appointment Duration)
export function calculateEndTime(startTime, serviceDurations) {

    const startMoment = moment(startTime, 'HH:mm');

    // Calculate the total duration in minutes
    const totalDurationMinutes = serviceDurations.reduce((sum, duration) => sum + Number(duration), 0);

    const endMoment = startMoment.add(totalDurationMinutes, 'minutes');
    const formattedEndTime = endMoment.format('HH:mm:ss');

    return formattedEndTime;
}
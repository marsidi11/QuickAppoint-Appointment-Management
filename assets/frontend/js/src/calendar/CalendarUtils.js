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

// Get the dates before the current date
export function pastDates(calendar) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    return calendar.flat().filter(day => new Date(day.date).setHours(0, 0, 0, 0) < today);
}
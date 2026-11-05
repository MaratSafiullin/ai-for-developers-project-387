/**
 * Formats the time of an ISO 8601 moment in the given timezone, e.g. «09:30».
 */
export function formatTime(isoDateTime: string, timeZone: string): string {
    return new Intl.DateTimeFormat('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone,
    }).format(new Date(isoDateTime));
}

/**
 * Formats the date of an ISO 8601 moment in the given timezone, e.g.
 * «среда, 4 ноября».
 */
export function formatDate(isoDateTime: string, timeZone: string): string {
    return new Intl.DateTimeFormat('ru-RU', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        timeZone,
    }).format(new Date(isoDateTime));
}

/**
 * Formats a calendar date like «2026-11-04» as «ср, 4 нояб.». The date has no
 * time or zone, so it is read and formatted in UTC to keep the same day.
 */
export function formatCalendarDate(date: string): string {
    return new Intl.DateTimeFormat('ru-RU', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
        timeZone: 'UTC',
    }).format(new Date(`${date}T00:00:00Z`));
}

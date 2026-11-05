/**
 * Formats a meeting duration in minutes, e.g. 90 → «1 ч 30 мин».
 */
export function formatDuration(minutes: number): string {
    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    return [
        hours > 0 ? `${hours} ч` : null,
        remainingMinutes > 0 ? `${remainingMinutes} мин` : null,
    ]
        .filter((part) => part !== null)
        .join(' ');
}

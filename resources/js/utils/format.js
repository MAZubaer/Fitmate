// Date helpers work with local "YYYY-MM-DD" keys so a date never shifts by
// a day because of UTC parsing.

export function toKey(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

export const todayKey = () => toKey(new Date());

export function parseKey(key) {
    if (!key) return null;
    const [y, m, d] = String(key).slice(0, 10).split('-').map(Number);
    if (!y || !m || !d) return null;
    return new Date(y, m - 1, d);
}

export function shiftKey(key, days) {
    const date = parseKey(key) ?? new Date();
    date.setDate(date.getDate() + days);
    return toKey(date);
}

/** The last `n` days (oldest first), ending today. */
export function lastNDays(n) {
    const today = todayKey();
    return Array.from({ length: n }, (_, i) => shiftKey(today, i - (n - 1)));
}

export function weekday(key) {
    const date = parseKey(key);
    return date ? date.toLocaleDateString('en-US', { weekday: 'short' }) : '';
}

export function formatDate(key, options = { month: 'short', day: 'numeric' }) {
    const date = parseKey(key);
    return date ? date.toLocaleDateString('en-US', options) : '';
}

export function dayDiff(key) {
    const date = parseKey(key);
    if (!date) return null;
    const today = parseKey(todayKey());
    return Math.round((date - today) / 86400000);
}

export function relativeDay(key) {
    const diff = dayDiff(key);
    if (diff === null) return '';
    if (diff === 0) return 'Today';
    if (diff === 1) return 'Tomorrow';
    if (diff === -1) return 'Yesterday';
    return formatDate(key, { weekday: 'short', month: 'short', day: 'numeric' });
}

/** "18:30" or "18:30:00" -> "6:30 PM" */
export function formatTime(value) {
    if (!value) return '';
    const [h, m] = String(value).split(':').map(Number);
    if (Number.isNaN(h)) return String(value);
    const date = new Date();
    date.setHours(h, m || 0, 0, 0);
    return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
}

/** Parses both ISO strings and "YYYY-MM-DD HH:MM:SS" database strings. */
export function parseDateTime(value) {
    if (!value) return null;
    const str = String(value);
    const date = new Date(str.includes('T') ? str : str.replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? null : date;
}

export function timeAgo(value) {
    const date = parseDateTime(value);
    if (!date) return '';
    const seconds = Math.round((Date.now() - date.getTime()) / 1000);
    if (seconds < 45) return 'just now';
    if (seconds < 3600) return `${Math.round(seconds / 60)} min ago`;
    if (seconds < 86400) return `${Math.round(seconds / 3600)}h ago`;
    if (seconds < 7 * 86400) return `${Math.round(seconds / 86400)}d ago`;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

export const formatNumber = (n) => Number(n || 0).toLocaleString('en-US');

export function formatDuration(seconds) {
    const minutes = Math.round((Number(seconds) || 0) / 60);
    if (minutes < 60) return `${minutes} min`;
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return m ? `${h}h ${m}m` : `${h}h`;
}

export function initials(name = '') {
    return (
        String(name)
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => part[0])
            .join('')
            .toUpperCase() || '?'
    );
}

export function greeting() {
    const hour = new Date().getHours();
    if (hour < 5) return 'Good night';
    if (hour < 12) return 'Good morning';
    if (hour < 17) return 'Good afternoon';
    return 'Good evening';
}

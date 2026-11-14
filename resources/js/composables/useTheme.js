import { ref } from 'vue';

const STORAGE_KEY = 'fitmate-theme';

// Shared across the whole app; the initial class is set by an inline script in app.blade.php.
const isDark = ref(
    typeof document !== 'undefined' && document.documentElement.classList.contains('dark'),
);

function setDark(dark) {
    isDark.value = dark;
    document.documentElement.classList.toggle('dark', dark);
    try {
        localStorage.setItem(STORAGE_KEY, dark ? 'dark' : 'light');
    } catch {
        // Storage can be unavailable (private mode); the theme still applies for this visit.
    }
}

export function useTheme() {
    return {
        isDark,
        setDark,
        toggle: () => setDark(!isDark.value),
    };
}

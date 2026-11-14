import { reactive } from 'vue';

const toasts = reactive([]);
let nextId = 0;

export function dismiss(id) {
    const index = toasts.findIndex((t) => t.id === id);
    if (index !== -1) toasts.splice(index, 1);
}

/**
 * Show a toast. type: 'success' | 'error' | 'info'
 */
export function toast(message, type = 'success', timeout = 3500) {
    if (!message) return;
    const id = ++nextId;
    toasts.push({ id, message, type });
    setTimeout(() => dismiss(id), timeout);
}

export function useToasts() {
    return { toasts, toast, dismiss };
}

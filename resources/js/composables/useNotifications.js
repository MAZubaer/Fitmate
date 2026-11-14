import { ref } from 'vue';
import axios from 'axios';

// Unread badge count shared by the app shell and the Notifications page.
export const unreadCount = ref(0);

export async function refreshUnread() {
    try {
        const res = await axios.get('/api/notifications/unread-count');
        unreadCount.value = Number(res.data) || 0;
    } catch {
        // Keep the last known value if the request fails.
    }
}

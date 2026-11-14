<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { toast } from '@/composables/useToast';
import { unreadCount } from '@/composables/useNotifications';
import { parseDateTime, timeAgo } from '@/utils/format';

const notifications = ref([]);
const loading = ref(true);
const filter = ref('all');

const isRead = (n) => [true, 1, '1', 't', 'true'].includes(n.read);

async function fetchNotifications() {
    try {
        const { data } = await axios.get('/api/notifications');
        notifications.value = data.map((n) => ({ ...n, read: isRead(n) }));
        unreadCount.value = notifications.value.filter((n) => !n.read).length;
    } catch {
        toast('Could not load notifications', 'error');
    } finally {
        loading.value = false;
    }
}

onMounted(fetchNotifications);

const unread = computed(() => notifications.value.filter((n) => !n.read));
const visible = computed(() => (filter.value === 'unread' ? unread.value : notifications.value));

const kind = (message = '') => {
    const m = message.toLowerCase();
    if (m.includes('workout')) return { icon: 'dumbbell', tone: 'bg-violet-500/10 text-violet-600 dark:text-violet-400' };
    if (m.includes('meal')) return { icon: 'utensils', tone: 'bg-brand-500/10 text-brand-600 dark:text-brand-400' };
    return { icon: 'bell', tone: 'bg-sky-500/10 text-sky-600 dark:text-sky-400' };
};

// Messages start with an emoji; the icon replaces it in this UI.
const cleanMessage = (message = '') => message.replace(/^[^\p{L}\p{N}]+/u, '');

const fullDate = (value) => parseDateTime(value)?.toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' }) ?? '';

async function markRead(n) {
    if (n.read) return;
    n.read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
    try {
        await axios.post(`/api/notifications/${n.id}/read`);
    } catch {
        n.read = false;
        unreadCount.value += 1;
    }
}

const markingAll = ref(false);
async function markAllRead() {
    markingAll.value = true;
    await Promise.allSettled(unread.value.map(markRead));
    markingAll.value = false;
    toast('All caught up');
}
</script>

<template>
    <AppLayout>
        <Head title="Notifications" />

        <div class="mx-auto max-w-3xl">
            <PageHeader title="Notifications" subtitle="Reminders for your scheduled meals and workouts.">
                <template #actions>
                    <button class="btn-secondary" :disabled="!unread.length || markingAll" @click="markAllRead">
                        <Icon name="check-check" class="h-4 w-4" /> Mark all as read
                    </button>
                </template>
            </PageHeader>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-line p-3 sm:px-4">
                    <div class="segmented">
                        <button :aria-pressed="filter === 'all'" @click="filter = 'all'">
                            All <span class="ml-1 text-xs text-muted">{{ notifications.length }}</span>
                        </button>
                        <button :aria-pressed="filter === 'unread'" @click="filter = 'unread'">
                            Unread <span class="ml-1 text-xs text-muted">{{ unread.length }}</span>
                        </button>
                    </div>
                </div>

                <div v-if="loading" class="divide-y divide-line">
                    <div v-for="i in 4" :key="i" class="flex gap-4 p-4 sm:px-5">
                        <div class="skeleton h-10 w-10 rounded-xl" />
                        <div class="flex-1 space-y-2"><div class="skeleton h-4 w-2/3" /><div class="skeleton h-3 w-1/4" /></div>
                    </div>
                </div>

                <EmptyState
                    v-else-if="visible.length === 0"
                    :icon="filter === 'unread' ? 'check-check' : 'bell'"
                    :title="filter === 'unread' ? 'You’re all caught up' : 'No notifications yet'"
                    :description="filter === 'unread' ? 'No unread reminders right now.' : 'Add a time to a meal or schedule a workout and reminders will show up here.'"
                />

                <ul v-else class="divide-y divide-line">
                    <li v-for="n in visible" :key="n.id">
                        <button
                            type="button"
                            class="flex w-full items-start gap-4 p-4 text-left transition hover:bg-subtle/60 sm:px-5"
                            :class="{ 'bg-brand-500/[0.04]': !n.read }"
                            @click="markRead(n)"
                        >
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl" :class="kind(n.message).tone">
                                <Icon :name="kind(n.message).icon" class="h-[18px] w-[18px]" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm" :class="n.read ? 'text-muted' : 'font-semibold text-ink'">{{ cleanMessage(n.message) }}</span>
                                <span class="mt-0.5 block text-xs text-muted" :title="fullDate(n.scheduled_at)">{{ timeAgo(n.scheduled_at) }}</span>
                            </span>
                            <span v-if="!n.read" class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-brand-500" aria-label="Unread" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>

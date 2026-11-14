<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import Logo from '@/Components/Logo.vue';
import ToastHost from '@/Components/ToastHost.vue';
import { useTheme } from '@/composables/useTheme';
import { toast } from '@/composables/useToast';
import { refreshUnread, unreadCount } from '@/composables/useNotifications';
import { initials } from '@/utils/format';

const props = defineProps({
    // Full-bleed pages (e.g. the AI chat) manage their own height and padding.
    fullBleed: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? {});
const { isDark, toggle: toggleTheme } = useTheme();

const drawerOpen = ref(false);
const userMenuOpen = ref(false);

const nav = [
    { label: 'Dashboard', href: '/dashboard', icon: 'dashboard', match: 'Dashboard' },
    { label: 'Workouts', href: '/workout', icon: 'dumbbell', match: 'Workout' },
    { label: 'Meals', href: '/meals', icon: 'utensils', match: 'Meals/' },
    { label: 'AI Coach', href: '/meal-assistant', icon: 'sparkles', match: 'MealAssistant' },
    { label: 'Notifications', href: '/notifications', icon: 'bell', match: 'Notifications', badge: true },
    { label: 'Reports', href: '/analytics/export', icon: 'download', match: 'AnalyticsExport' },
];

const tabs = nav.filter((item) => item.label !== 'Reports');

const isActive = (item) => page.component.startsWith(item.match);

const today = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });

// Close menus whenever the page changes.
watch(
    () => page.url,
    () => {
        drawerOpen.value = false;
        userMenuOpen.value = false;
    },
);

// Show server flash messages (e.g. "Meal saved!") as toasts.
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast(flash.success, 'success');
        if (flash?.error) toast(flash.error, 'error');
    },
    { immediate: true },
);

onMounted(refreshUnread);
</script>

<template>
    <div class="min-h-screen bg-canvas">
        <!-- Desktop sidebar -->
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-64 flex-col border-r border-line bg-surface lg:flex">
            <div class="flex h-16 items-center px-6">
                <Link href="/dashboard" aria-label="FitMate home"><Logo /></Link>
            </div>

            <nav class="scrollbar-thin flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <p class="eyebrow px-3 pb-2">Menu</p>
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition"
                    :class="isActive(item) ? 'bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'text-muted hover:bg-subtle hover:text-ink'"
                >
                    <span
                        v-if="isActive(item)"
                        class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-brand-500"
                    />
                    <Icon :name="item.icon" class="h-[18px] w-[18px]" />
                    <span class="flex-1">{{ item.label }}</span>
                    <span
                        v-if="item.badge && unreadCount > 0"
                        class="rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white"
                    >
                        {{ unreadCount > 99 ? '99+' : unreadCount }}
                    </span>
                </Link>
            </nav>

            <!-- AI promo -->
            <Link
                v-if="!page.component.startsWith('MealAssistant')"
                href="/meal-assistant"
                class="group relative mx-3 mb-3 overflow-hidden rounded-2xl bg-slate-950 p-4 text-white"
            >
                <div class="absolute -right-6 -top-6 h-24 w-24 rounded-full bg-brand-500/40 blur-2xl transition group-hover:bg-brand-400/50" />
                <Icon name="sparkles" class="relative h-5 w-5 text-brand-300" />
                <p class="relative mt-3 text-sm font-semibold">Ask your AI coach</p>
                <p class="relative mt-0.5 text-xs text-slate-400">Personal advice based on your real data.</p>
            </Link>

            <div class="border-t border-line p-3">
                <div class="flex items-center gap-3 rounded-xl p-2">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-400 to-teal-600 text-xs font-bold text-white">
                        {{ initials(user.name) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ user.name }}</p>
                        <p class="truncate text-xs text-muted">{{ user.email }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="btn-icon" aria-label="Log out" title="Log out">
                        <Icon name="logout" class="h-[18px] w-[18px]" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Mobile drawer -->
        <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" leave-active-class="transition-opacity duration-200" leave-to-class="opacity-0">
            <div v-if="drawerOpen" class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-sm lg:hidden" @click="drawerOpen = false" />
        </Transition>
        <Transition enter-active-class="transition duration-300 ease-out" enter-from-class="-translate-x-full" leave-active-class="transition duration-200 ease-in" leave-to-class="-translate-x-full">
            <aside v-if="drawerOpen" class="fixed inset-y-0 left-0 z-50 flex w-72 max-w-[85vw] flex-col bg-surface shadow-2xl lg:hidden">
                <div class="flex h-16 items-center justify-between px-5">
                    <Logo />
                    <button class="btn-icon" aria-label="Close menu" @click="drawerOpen = false"><Icon name="x" /></button>
                </div>
                <div class="mx-4 mb-2 flex items-center gap-3 rounded-2xl bg-subtle p-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-gradient-to-br from-brand-400 to-teal-600 text-sm font-bold text-white">
                        {{ initials(user.name) }}
                    </span>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ user.name }}</p>
                        <p class="truncate text-xs text-muted">{{ user.email }}</p>
                    </div>
                </div>
                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
                    <Link
                        v-for="item in [...nav, { label: 'Profile & settings', href: '/profile', icon: 'settings', match: 'Profile' }]"
                        :key="item.href"
                        :href="item.href"
                        class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition"
                        :class="isActive(item) ? 'bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'text-muted hover:bg-subtle hover:text-ink'"
                    >
                        <Icon :name="item.icon" class="h-[18px] w-[18px]" />
                        <span class="flex-1">{{ item.label }}</span>
                        <span v-if="item.badge && unreadCount > 0" class="rounded-full bg-rose-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ unreadCount }}</span>
                    </Link>
                </nav>
                <div class="border-t border-line p-3 pb-safe">
                    <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-rose-600 hover:bg-rose-500/10 dark:text-rose-400">
                        <Icon name="logout" class="h-[18px] w-[18px]" /> Log out
                    </Link>
                </div>
            </aside>
        </Transition>

        <div class="flex min-h-screen flex-col lg:pl-64">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-line bg-surface/80 px-4 backdrop-blur-xl sm:px-6 lg:px-8">
                <button class="btn-icon -ml-2 lg:hidden" aria-label="Open menu" @click="drawerOpen = true">
                    <Icon name="menu" />
                </button>
                <Link href="/dashboard" class="lg:hidden" aria-label="FitMate home"><Logo /></Link>
                <p class="hidden text-sm text-muted lg:block">{{ today }}</p>

                <div class="ml-auto flex items-center gap-1">
                    <button class="btn-icon" :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'" :title="isDark ? 'Light mode' : 'Dark mode'" @click="toggleTheme">
                        <Icon :name="isDark ? 'sun' : 'moon'" class="h-[18px] w-[18px]" />
                    </button>
                    <Link href="/notifications" class="btn-icon relative" aria-label="Notifications">
                        <Icon name="bell" class="h-[18px] w-[18px]" />
                        <span v-if="unreadCount > 0" class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-surface" />
                    </Link>

                    <div class="relative ml-1">
                        <button
                            class="flex items-center gap-2 rounded-full p-0.5 pr-2 transition hover:bg-subtle"
                            :aria-expanded="userMenuOpen"
                            aria-haspopup="menu"
                            @click="userMenuOpen = !userMenuOpen"
                        >
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-gradient-to-br from-brand-400 to-teal-600 text-xs font-bold text-white">
                                {{ initials(user.name) }}
                            </span>
                            <Icon name="chevron-down" class="hidden h-4 w-4 text-muted sm:block" />
                        </button>

                        <div v-if="userMenuOpen" class="fixed inset-0 z-40" @click="userMenuOpen = false" />
                        <Transition enter-active-class="transition duration-150 ease-out" enter-from-class="scale-95 opacity-0" leave-active-class="transition duration-100 ease-in" leave-to-class="scale-95 opacity-0">
                            <div v-if="userMenuOpen" class="absolute right-0 z-50 mt-2 w-60 origin-top-right rounded-2xl border border-line bg-surface p-1.5 shadow-lift" role="menu">
                                <div class="px-3 py-2.5">
                                    <p class="truncate text-sm font-semibold">{{ user.name }}</p>
                                    <p class="truncate text-xs text-muted">{{ user.email }}</p>
                                </div>
                                <div class="my-1 h-px bg-line" />
                                <Link href="/profile" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-ink hover:bg-subtle" role="menuitem">
                                    <Icon name="user" class="h-4 w-4 text-muted" /> Profile & settings
                                </Link>
                                <Link href="/analytics/export" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-ink hover:bg-subtle" role="menuitem">
                                    <Icon name="download" class="h-4 w-4 text-muted" /> Export data
                                </Link>
                                <div class="my-1 h-px bg-line" />
                                <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-rose-600 hover:bg-rose-500/10 dark:text-rose-400" role="menuitem">
                                    <Icon name="logout" class="h-4 w-4" /> Log out
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <main v-if="props.fullBleed" class="flex min-h-0 flex-1 flex-col">
                <slot />
            </main>
            <main v-else class="flex-1 px-4 pb-28 pt-6 sm:px-6 sm:pt-8 lg:px-8 lg:pb-12">
                <div class="mx-auto w-full max-w-7xl animate-fade-up">
                    <slot />
                </div>
            </main>
        </div>

        <!-- Mobile tab bar -->
        <nav
            v-if="!props.fullBleed"
            class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-line bg-surface/90 backdrop-blur-xl lg:hidden"
            aria-label="Primary"
        >
            <div class="mx-auto grid max-w-md grid-cols-5">
                <Link
                    v-for="item in tabs"
                    :key="item.href"
                    :href="item.href"
                    class="relative flex flex-col items-center gap-1 py-2.5 text-[11px] font-medium transition"
                    :class="isActive(item) ? 'text-brand-600 dark:text-brand-400' : 'text-muted'"
                >
                    <span class="relative">
                        <Icon :name="item.icon" class="h-[22px] w-[22px]" :stroke-width="isActive(item) ? 2.4 : 2" />
                        <span v-if="item.badge && unreadCount > 0" class="absolute -right-1.5 -top-1 h-2.5 w-2.5 rounded-full bg-rose-500 ring-2 ring-surface" />
                    </span>
                    {{ item.label === 'Notifications' ? 'Alerts' : item.label === 'AI Coach' ? 'Coach' : item.label }}
                </Link>
            </div>
        </nav>

        <ToastHost />
    </div>
</template>

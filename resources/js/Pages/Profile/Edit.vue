<script setup>
import { computed } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { useTheme } from '@/composables/useTheme';
import { initials } from '@/utils/format';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status: { type: String },
});

const user = computed(() => usePage().props.auth.user);
const { isDark, setDark } = useTheme();
const memberSince = computed(() =>
    user.value.created_at ? new Date(user.value.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : '',
);
</script>

<template>
    <AppLayout>
        <Head title="Profile" />

        <div class="mx-auto max-w-4xl">
            <PageHeader title="Profile & settings" subtitle="Manage your personal details, appearance and account security." />

            <!-- Summary -->
            <div class="card card-pad flex flex-col items-start gap-5 sm:flex-row sm:items-center">
                <span class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-brand-400 to-teal-600 text-xl font-bold text-white shadow-lg shadow-brand-600/20">
                    {{ initials(user.name) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-lg font-semibold">{{ user.name }}<span v-if="user.nickname" class="font-normal text-muted"> · {{ user.nickname }}</span></p>
                    <p class="truncate text-sm text-muted">{{ user.email }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="badge-neutral"><Icon name="calendar" class="h-3.5 w-3.5" /> Joined {{ memberSince }}</span>
                    <span class="badge-brand"><Icon name="flame" class="h-3.5 w-3.5" /> {{ user.meal_streak ?? 0 }}-day streak</span>
                </div>
            </div>

            <div class="mt-8 space-y-10">
                <section class="grid gap-4 md:grid-cols-3 md:gap-8">
                    <div>
                        <h2 class="card-title">Personal information</h2>
                        <p class="mt-1 text-sm text-muted">Your name, email and basic details used to personalise FitMate.</p>
                    </div>
                    <div class="card card-pad md:col-span-2">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" />
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-3 md:gap-8">
                    <div>
                        <h2 class="card-title">Appearance</h2>
                        <p class="mt-1 text-sm text-muted">Choose how FitMate looks on this device.</p>
                    </div>
                    <div class="card card-pad md:col-span-2">
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                v-for="option in [{ dark: false, label: 'Light', icon: 'sun' }, { dark: true, label: 'Dark', icon: 'moon' }]"
                                :key="option.label"
                                type="button"
                                class="group rounded-2xl border p-3 text-left transition"
                                :class="isDark === option.dark ? 'border-brand-500 ring-4 ring-brand-500/10' : 'border-line hover:border-muted/40'"
                                :aria-pressed="isDark === option.dark"
                                @click="setDark(option.dark)"
                            >
                                <div class="overflow-hidden rounded-xl border border-black/5" :class="option.dark ? 'bg-slate-900' : 'bg-slate-100'">
                                    <div class="flex gap-1.5 p-2.5">
                                        <div class="h-12 w-6 rounded-md" :class="option.dark ? 'bg-slate-800' : 'bg-white'" />
                                        <div class="flex-1 space-y-1.5">
                                            <div class="h-3 w-2/3 rounded" :class="option.dark ? 'bg-slate-700' : 'bg-white'" />
                                            <div class="h-7 rounded-md" :class="option.dark ? 'bg-slate-800' : 'bg-white'" />
                                        </div>
                                    </div>
                                </div>
                                <span class="mt-2.5 flex items-center gap-2 text-sm font-medium">
                                    <Icon :name="option.icon" class="h-4 w-4 text-muted" /> {{ option.label }}
                                </span>
                            </button>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-3 md:gap-8">
                    <div>
                        <h2 class="card-title">Password</h2>
                        <p class="mt-1 text-sm text-muted">Use a long, unique password to keep your account secure.</p>
                    </div>
                    <div class="card card-pad md:col-span-2">
                        <UpdatePasswordForm />
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-3 md:gap-8">
                    <div>
                        <h2 class="card-title text-rose-600 dark:text-rose-400">Danger zone</h2>
                        <p class="mt-1 text-sm text-muted">Permanently delete your account and all of its data.</p>
                    </div>
                    <div class="card card-pad border-rose-500/30 md:col-span-2">
                        <DeleteUserForm />
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { formatDate, parseKey, shiftKey, todayKey, toKey } from '@/utils/format';

const presets = [
    { id: '7', label: 'Last 7 days' },
    { id: '30', label: 'Last 30 days' },
    { id: '90', label: 'Last 90 days' },
    { id: 'month', label: 'This month' },
    { id: 'custom', label: 'Custom' },
];

const preset = ref('30');
const from = ref(shiftKey(todayKey(), -29));
const to = ref(todayKey());
const format = ref('pdf');
const error = ref('');

function applyPreset(id) {
    preset.value = id;
    error.value = '';
    const today = todayKey();
    if (id === 'month') {
        const d = new Date();
        from.value = toKey(new Date(d.getFullYear(), d.getMonth(), 1));
        to.value = today;
    } else if (id !== 'custom') {
        from.value = shiftKey(today, -(Number(id) - 1));
        to.value = today;
    }
}

const dayCount = computed(() => {
    const a = parseKey(from.value);
    const b = parseKey(to.value);
    return a && b ? Math.round((b - a) / 86400000) + 1 : 0;
});

const formats = [
    { id: 'pdf', icon: 'file-text', title: 'PDF report', description: 'A formatted health report, ready to print or share.' },
    { id: 'csv', icon: 'table', title: 'CSV spreadsheet', description: 'Raw rows for Excel, Google Sheets or your own analysis.' },
];

function download() {
    error.value = '';
    if (!from.value || !to.value) {
        error.value = 'Choose both a start and an end date.';
        return;
    }
    if (from.value > to.value) {
        error.value = 'The start date must be on or before the end date.';
        return;
    }
    const params = new URLSearchParams({ from: from.value, to: to.value });
    window.location.href = `/analytics/export/${format.value}?${params}`;
}
</script>

<template>
    <AppLayout>
        <Head title="Export data" />

        <div class="mx-auto max-w-3xl">
            <PageHeader title="Export your data" subtitle="Download your meals, workouts, steps, water and BMI for any period." />

            <div class="card card-pad space-y-8">
                <section>
                    <h2 class="card-title">1. Choose a period</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            v-for="p in presets"
                            :key="p.id"
                            type="button"
                            class="rounded-full border px-3.5 py-1.5 text-sm font-medium transition"
                            :class="preset === p.id ? 'border-brand-500 bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'border-line text-muted hover:text-ink'"
                            @click="applyPreset(p.id)"
                        >
                            {{ p.label }}
                        </button>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <label class="label" for="from">From</label>
                            <input id="from" v-model="from" type="date" class="input" :max="to || undefined" @input="preset = 'custom'" />
                        </div>
                        <div>
                            <label class="label" for="to">To</label>
                            <input id="to" v-model="to" type="date" class="input" :min="from || undefined" @input="preset = 'custom'" />
                        </div>
                    </div>
                    <p v-if="dayCount > 0" class="mt-3 flex items-center gap-1.5 text-sm text-muted">
                        <Icon name="calendar" class="h-4 w-4" />
                        {{ formatDate(from, { month: 'short', day: 'numeric', year: 'numeric' }) }} – {{ formatDate(to, { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        · {{ dayCount }} day{{ dayCount === 1 ? '' : 's' }}
                    </p>
                </section>

                <section>
                    <h2 class="card-title">2. Pick a format</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label="Export format">
                        <button
                            v-for="f in formats"
                            :key="f.id"
                            type="button"
                            role="radio"
                            :aria-checked="format === f.id"
                            class="relative flex items-start gap-3 rounded-2xl border p-4 text-left transition"
                            :class="format === f.id ? 'border-brand-500 bg-brand-500/5 ring-4 ring-brand-500/10' : 'border-line hover:border-muted/40'"
                            @click="format = f.id"
                        >
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl" :class="format === f.id ? 'bg-brand-500/15 text-brand-600 dark:text-brand-400' : 'bg-subtle text-muted'">
                                <Icon :name="f.icon" />
                            </span>
                            <span class="pr-6">
                                <span class="block text-sm font-semibold">{{ f.title }}</span>
                                <span class="mt-0.5 block text-xs text-muted">{{ f.description }}</span>
                            </span>
                            <span
                                class="absolute right-4 top-4 grid h-5 w-5 place-items-center rounded-full border-2 transition"
                                :class="format === f.id ? 'border-brand-500 bg-brand-500 text-white' : 'border-line'"
                            >
                                <Icon v-if="format === f.id" name="check" class="h-3 w-3" :stroke-width="3.5" />
                            </span>
                        </button>
                    </div>
                </section>

                <div class="flex flex-col gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
                    <p v-if="error" class="field-error mt-0">{{ error }}</p>
                    <p v-else class="text-sm text-muted">Your BMI export uses your most recent measurement.</p>
                    <button class="btn-primary" @click="download">
                        <Icon name="download" class="h-4 w-4" /> Download {{ format.toUpperCase() }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

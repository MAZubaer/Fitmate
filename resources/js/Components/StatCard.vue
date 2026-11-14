<script setup>
import { computed } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    unit: { type: String, default: '' },
    hint: { type: String, default: '' },
    icon: { type: String, required: true },
    tone: { type: String, default: 'brand' },
    // 0-100, or null to hide the progress bar
    progress: { type: Number, default: null },
});

const tones = {
    brand: { icon: 'bg-brand-500/10 text-brand-600 dark:text-brand-400', bar: 'bg-brand-500' },
    orange: { icon: 'bg-orange-500/10 text-orange-600 dark:text-orange-400', bar: 'bg-orange-500' },
    amber: { icon: 'bg-amber-500/10 text-amber-600 dark:text-amber-400', bar: 'bg-amber-500' },
    sky: { icon: 'bg-sky-500/10 text-sky-600 dark:text-sky-400', bar: 'bg-sky-500' },
    violet: { icon: 'bg-violet-500/10 text-violet-600 dark:text-violet-400', bar: 'bg-violet-500' },
    rose: { icon: 'bg-rose-500/10 text-rose-600 dark:text-rose-400', bar: 'bg-rose-500' },
};

const t = computed(() => tones[props.tone] ?? tones.brand);
const width = computed(() => `${Math.max(0, Math.min(100, props.progress ?? 0))}%`);
</script>

<template>
    <div class="card card-pad flex flex-col">
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-xl" :class="t.icon">
                    <Icon :name="icon" />
                </span>
                <p class="text-sm font-medium text-muted">{{ label }}</p>
            </div>
            <slot name="action" />
        </div>

        <p class="mt-4 text-3xl font-bold tracking-tight tabular-nums">
            {{ value }}<span v-if="unit" class="ml-1 text-base font-semibold text-muted">{{ unit }}</span>
        </p>

        <div v-if="progress !== null" class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-subtle">
            <div class="h-full rounded-full transition-all duration-700" :class="t.bar" :style="{ width }" />
        </div>

        <p v-if="hint" class="mt-2 text-xs text-muted">{{ hint }}</p>
    </div>
</template>

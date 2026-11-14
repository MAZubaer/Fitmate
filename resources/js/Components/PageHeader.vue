<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    eyebrow: { type: String, default: '' },
});
</script>

<template>
    <div class="mb-6 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            <p v-if="eyebrow" class="eyebrow mb-1.5">{{ eyebrow }}</p>
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ title }}</h1>
            <p v-if="subtitle" class="mt-1.5 text-sm text-muted sm:text-base">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>

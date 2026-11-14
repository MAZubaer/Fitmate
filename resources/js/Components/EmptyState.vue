<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    icon: { type: String, default: 'sparkles' },
    title: { type: String, required: true },
    description: { type: String, default: '' },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
        <div class="grid h-14 w-14 place-items-center rounded-2xl bg-subtle text-muted">
            <Icon :name="icon" class="h-6 w-6" />
        </div>
        <h3 class="mt-4 font-semibold">{{ title }}</h3>
        <p v-if="description" class="mt-1 max-w-sm text-sm text-muted">{{ description }}</p>
        <div v-if="$slots.default" class="mt-5">
            <slot />
        </div>
    </div>
</template>

<script setup>
import Icon from '@/Components/Icon.vue';

defineProps({
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <span class="inline-flex items-center gap-2.5">
        <span
            class="grid h-9 w-9 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 text-white shadow-lg shadow-brand-600/25"
        >
            <Icon name="activity" class="h-5 w-5" :stroke-width="2.5" />
        </span>
        <span v-if="!compact" class="text-lg font-extrabold tracking-tight">
            Fit<span class="text-brand-500">Mate</span>
        </span>
    </span>
</template>

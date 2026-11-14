<script setup>
import Icon from '@/Components/Icon.vue';
import { useToasts } from '@/composables/useToast';

const { toasts, dismiss } = useToasts();

const styles = {
    success: { icon: 'check-circle', tone: 'text-brand-500' },
    error: { icon: 'alert-circle', tone: 'text-rose-500' },
    info: { icon: 'info', tone: 'text-sky-500' },
};
</script>

<template>
    <div
        aria-live="polite"
        class="pointer-events-none fixed inset-x-0 top-3 z-[100] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:bottom-6 sm:right-6 sm:top-auto sm:items-end"
    >
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0 sm:translate-x-4 sm:translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-to-class="opacity-0"
            move-class="transition duration-300"
        >
            <div
                v-for="t in toasts"
                :key="t.id"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl border border-line bg-surface/95 p-3.5 pr-2 shadow-lift backdrop-blur"
                role="status"
            >
                <Icon :name="styles[t.type]?.icon ?? 'info'" :class="['mt-0.5', styles[t.type]?.tone]" />
                <p class="flex-1 text-sm font-medium text-ink">{{ t.message }}</p>
                <button class="btn-icon -my-1 h-7 w-7" aria-label="Dismiss" @click="dismiss(t.id)">
                    <Icon name="x" class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

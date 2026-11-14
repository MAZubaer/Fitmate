<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import Icon from '@/Components/Icon.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    maxWidth: { type: String, default: 'lg' },
    closeable: { type: Boolean, default: true },
    // When a title is given, the modal renders a header and pads its body.
    title: { type: String, default: '' },
    description: { type: String, default: '' },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;
            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';
            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) emit('close');
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();
        if (props.show) close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
});

const maxWidthClass = computed(
    () =>
        ({
            sm: 'sm:max-w-sm',
            md: 'sm:max-w-md',
            lg: 'sm:max-w-lg',
            xl: 'sm:max-w-xl',
            '2xl': 'sm:max-w-2xl',
        })[props.maxWidth],
);
</script>

<template>
    <dialog
        ref="dialog"
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
    >
        <div class="fixed inset-0 z-50 flex items-end justify-center overflow-y-auto p-0 sm:items-center sm:p-6" scroll-region>
            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-show="show" class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm transition-opacity" @click="close" />
            </Transition>

            <Transition
                enter-active-class="ease-out duration-300"
                enter-from-class="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95"
                enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                leave-to-class="opacity-0 translate-y-6 sm:translate-y-2 sm:scale-95"
            >
                <div
                    v-show="show"
                    class="relative max-h-[92dvh] w-full transform overflow-y-auto rounded-t-3xl border border-line bg-surface text-ink shadow-2xl transition-all sm:rounded-2xl"
                    :class="maxWidthClass"
                    role="dialog"
                    aria-modal="true"
                >
                    <template v-if="showSlot">
                        <template v-if="title">
                            <div class="flex items-start justify-between gap-4 px-6 pt-6">
                                <div>
                                    <h2 class="text-lg font-semibold tracking-tight">{{ title }}</h2>
                                    <p v-if="description" class="mt-1 text-sm text-muted">{{ description }}</p>
                                </div>
                                <button v-if="closeable" class="btn-icon -mr-2 -mt-1" aria-label="Close" @click="close">
                                    <Icon name="x" />
                                </button>
                            </div>
                            <div class="px-6 pb-6 pt-5">
                                <slot />
                            </div>
                        </template>
                        <slot v-else />
                    </template>
                </div>
            </Transition>
        </div>
    </dialog>
</template>

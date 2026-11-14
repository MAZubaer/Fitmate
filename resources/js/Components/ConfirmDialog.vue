<script setup>
import Modal from '@/Components/Modal.vue';
import Icon from '@/Components/Icon.vue';

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Are you sure?' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Delete' },
    processing: { type: Boolean, default: false },
});

defineEmits(['confirm', 'close']);
</script>

<template>
    <Modal :show="show" max-width="sm" @close="$emit('close')">
        <div class="p-6">
            <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-rose-500/10 text-rose-600 dark:text-rose-400">
                <Icon name="trash" />
            </div>
            <h2 class="mt-4 text-center text-lg font-semibold">{{ title }}</h2>
            <p v-if="message" class="mt-1.5 text-center text-sm text-muted">{{ message }}</p>
            <div class="mt-6 grid grid-cols-2 gap-3">
                <button type="button" class="btn-secondary" @click="$emit('close')">Cancel</button>
                <button type="button" class="btn-danger" :disabled="processing" @click="$emit('confirm')">
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref } from 'vue';

// Google-only accounts have no password, so they confirm by typing DELETE.
const hasPassword = computed(() => usePage().props.auth.user?.has_password !== false);

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
    confirmation: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
        <header>
            <h2 class="text-base font-semibold text-ink">
                Delete Account
            </h2>

            <p class="mt-1 text-sm text-muted">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Delete Account</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2
                    class="text-base font-semibold text-ink"
                >
                    Are you sure you want to delete your account?
                </h2>

                <p class="mt-1 text-sm text-muted">
                    Once your account is deleted, all of its resources and data
                    will be permanently deleted.
                    <template v-if="hasPassword">Please enter your password to confirm you would like to permanently delete your account.</template>
                    <template v-else>Type <strong class="font-semibold text-ink">DELETE</strong> to confirm.</template>
                </p>

                <div v-if="!hasPassword" class="mt-6">
                    <InputLabel for="confirmation" value="Confirmation" class="sr-only" />
                    <TextInput
                        id="confirmation"
                        ref="passwordInput"
                        v-model="form.confirmation"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="DELETE"
                        autocomplete="off"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.confirmation" class="mt-2" />
                </div>

                <div v-else class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Cancel
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"

                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Delete Account
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>

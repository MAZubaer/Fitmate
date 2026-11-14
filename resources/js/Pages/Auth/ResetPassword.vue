<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout title="Choose a new password" subtitle="Make it strong — at least 8 characters.">
        <Head title="Reset Password" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" required autofocus autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="New password" />
                <PasswordInput id="password" v-model="form.password" required autocomplete="new-password" />
                <InputError :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" value="Confirm new password" />
                <PasswordInput id="password_confirmation" v-model="form.password_confirmation" required autocomplete="new-password" />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">Reset password</button>
        </form>
    </GuestLayout>
</template>

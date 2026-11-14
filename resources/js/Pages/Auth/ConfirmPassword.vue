<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout title="Confirm your password" subtitle="This is a secure area. Please confirm your password to continue.">
        <Head title="Confirm Password" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="password" value="Password" />
                <PasswordInput id="password" v-model="form.password" required autocomplete="current-password" autofocus />
                <InputError :message="form.errors.password" />
            </div>

            <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">Confirm</button>
        </form>
    </GuestLayout>
</template>

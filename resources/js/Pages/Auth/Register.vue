<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GoogleButton from '@/Components/GoogleButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout title="Create your account" subtitle="Start tracking workouts, meals and more — it takes a minute.">
        <Head title="Register" />

        <GoogleButton label="Sign up with Google" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Full name" />
                <TextInput id="name" v-model="form.name" type="text" placeholder="Alex Rahman" required autofocus autocomplete="name" />
                <InputError :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" placeholder="you@example.com" required autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <InputLabel for="password" value="Password" />
                    <PasswordInput id="password" v-model="form.password" required autocomplete="new-password" placeholder="8+ characters" />
                    <InputError :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel for="password_confirmation" value="Confirm" />
                    <PasswordInput id="password_confirmation" v-model="form.password_confirmation" required autocomplete="new-password" placeholder="Repeat password" />
                    <InputError :message="form.errors.password_confirmation" />
                </div>
            </div>

            <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Creating account…' : 'Create account' }}
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-muted">
            Already have an account?
            <Link :href="route('login')" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">Log in</Link>
        </p>
    </GuestLayout>
</template>

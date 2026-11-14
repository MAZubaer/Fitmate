<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import GoogleButton from '@/Components/GoogleButton.vue';
import Icon from '@/Components/Icon.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const flashError = computed(() => usePage().props.flash?.error);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout title="Welcome back" subtitle="Log in to pick up where you left off.">
        <Head title="Log in" />

        <div v-if="status" class="mb-6 flex items-start gap-2 rounded-xl bg-brand-500/10 p-3 text-sm font-medium text-brand-700 dark:text-brand-300">
            <Icon name="check-circle" class="h-4 w-4" /> {{ status }}
        </div>
        <div v-if="flashError" class="mb-6 flex items-start gap-2 rounded-xl bg-rose-500/10 p-3 text-sm font-medium text-rose-700 dark:text-rose-300">
            <Icon name="alert-circle" class="h-4 w-4" /> {{ flashError }}
        </div>

        <GoogleButton />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" placeholder="you@example.com" required autofocus autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        Forgot password?
                    </Link>
                </div>
                <PasswordInput id="password" v-model="form.password" required autocomplete="current-password" placeholder="••••••••" />
                <InputError :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-2.5">
                <Checkbox v-model:checked="form.remember" name="remember" />
                <span class="text-sm text-muted">Keep me logged in</span>
            </label>

            <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Logging in…' : 'Log in' }}
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-muted">
            New to FitMate?
            <Link :href="route('register')" class="font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">Create an account</Link>
        </p>
    </GuestLayout>
</template>

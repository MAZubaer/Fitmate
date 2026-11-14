<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    status: { type: String },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout title="Reset your password" subtitle="Enter your email and we'll send you a link to choose a new password.">
        <Head title="Forgot Password" />

        <div v-if="status" class="mb-6 flex items-start gap-2 rounded-xl bg-brand-500/10 p-3 text-sm font-medium text-brand-700 dark:text-brand-300">
            <Icon name="check-circle" class="h-4 w-4" /> {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" v-model="form.email" type="email" placeholder="you@example.com" required autofocus autocomplete="username" />
                <InputError :message="form.errors.email" />
            </div>

            <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">
                {{ form.processing ? 'Sending…' : 'Email reset link' }}
            </button>
        </form>

        <Link :href="route('login')" class="mt-8 flex items-center justify-center gap-1 text-sm font-medium text-muted hover:text-ink">
            <Icon name="chevron-left" class="h-4 w-4" /> Back to log in
        </Link>
    </GuestLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout title="Check your inbox" subtitle="We sent a verification link to your email. Click it to activate your account.">
        <Head title="Email Verification" />

        <div v-if="verificationLinkSent" class="mb-6 flex items-start gap-2 rounded-xl bg-brand-500/10 p-3 text-sm font-medium text-brand-700 dark:text-brand-300">
            <Icon name="check-circle" class="h-4 w-4" /> A new verification link has been sent to your email address.
        </div>

        <form class="space-y-3" @submit.prevent="submit">
            <button type="submit" class="btn-primary w-full py-3" :disabled="form.processing">Resend verification email</button>
            <Link :href="route('logout')" method="post" as="button" class="btn-ghost w-full">Log out</Link>
        </form>
    </GuestLayout>
</template>

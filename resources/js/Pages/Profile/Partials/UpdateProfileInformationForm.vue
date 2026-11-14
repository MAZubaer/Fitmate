<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    nickname: user.nickname ?? '',
    age: user.age ?? '',
    gender: user.gender ?? '',
});
</script>

<template>
    <section>
        <header class="sr-only">
            <h2 class="text-base font-semibold text-ink">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-muted">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="grid gap-5 sm:grid-cols-2"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="nickname" value="Nickname" />
                <TextInput
                    id="nickname"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.nickname"
                    autocomplete="nickname"
                />
                <InputError class="mt-2" :message="form.errors.nickname" />
            </div>

            <div>
                <InputLabel for="age" value="Age" />
                <TextInput
                    id="age"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.age"
                    min="0"
                    max="120"
                    autocomplete="age"
                />
                <InputError class="mt-2" :message="form.errors.age" />
            </div>

            <div>
                <InputLabel for="gender" value="Gender" />
                <select
                    id="gender"
                    v-model="form.gender"
                    class="input mt-1"
                >
                    <option value="">Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
                <InputError class="mt-2" :message="form.errors.gender" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null" class="sm:col-span-2">
                <p class="mt-2 text-sm text-ink">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="font-medium text-brand-600 underline hover:text-brand-700 dark:text-brand-400"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-brand-600 dark:text-brand-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4 sm:col-span-2">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-muted"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

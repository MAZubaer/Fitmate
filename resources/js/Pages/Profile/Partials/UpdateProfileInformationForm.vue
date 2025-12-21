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
    nickname: user.nickname || '',
    age: user.age || null,
    gender: user.gender || '',
    avatar: null,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div class="flex items-start gap-4">
                <div class="shrink-0">
                    <img
                        v-if="user.profile_photo_url"
                        :src="user.profile_photo_url"
                        alt="Avatar"
                        class="h-16 w-16 rounded-full object-cover"
                    />
                    <div v-else class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                        {{ user.name.charAt(0) }}
                    </div>
                </div>

                <div class="flex-1">
                    <InputLabel for="avatar" value="Profile Photo" />
                    <input id="avatar" type="file" class="mt-1" @change="e => form.avatar = e.target.files[0]" />
                    <InputError class="mt-2" :message="form.errors.avatar" />
                </div>
            </div>

            <div>
                <InputLabel for="display_name" value="Name" />
                <div class="mt-1 text-gray-700">{{ user.name }}</div>
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

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="age" value="Age" />
                    <TextInput id="age" type="number" class="mt-1 block w-full" v-model="form.age" />
                    <InputError class="mt-2" :message="form.errors.age" />
                </div>

                <div>
                    <InputLabel for="gender" value="Gender" />
                    <select id="gender" v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300">
                        <option value="">Select</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer_not_say">Prefer not to say</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.gender" />
                </div>
            </div>

            <div>
                <InputLabel for="display_email" value="Email" />
                <div class="mt-1 text-gray-700">{{ user.email }}</div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>

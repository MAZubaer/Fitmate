<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center overflow-y-auto bg-gray-100 px-4 py-6 sm:justify-center sm:py-8"
    >
        <div>
            <Link href="/" class="shrink-0">
                <ApplicationLogo class="h-16 w-16 fill-current text-gray-500 sm:h-20 sm:w-20" />
            </Link>
        </div>

        <div
            class="mt-6 w-full max-w-md overflow-hidden bg-white px-4 py-4 shadow-md sm:px-6 sm:py-5 sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const FitmateLogo = "/images/fitmate-logo.jpg";
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-white text-gray-900 flex flex-col">

        <!-- Top Navigation / Logo -->
        <header class="flex items-center px-8 py-4 shadow-md">
            <img 
                :src="FitmateLogo" 
                alt="FitMate Logo" 
                class="w-24 h-auto object-contain"
            />
        </header>

        <!-- Main Content -->
        <main class="flex flex-col items-center justify-center flex-1 px-6 py-10 text-center">

            <h1 class="text-4xl md:text-5xl font-extrabold mb-8">
                Welcome to <span class="text-blue-600">FitMate</span>
            </h1>

            <div class="max-w-2xl space-y-6 text-xl font-medium">

                <p class="italic">“The body achieves what the mind believes.”</p>
                <p class="italic">“Don’t limit your challenges — challenge your limits.”</p>
                <p class="italic">“Success starts with self-discipline.”</p>
                <p class="italic">“Train like a beast. Look like a beauty.”</p>

            </div>

            <div class="mt-10 flex gap-4">
                <Link 
                    href="/login"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700"
                >
                    Log In
                </Link>

                <Link 
                    href="/register"
                    class="px-6 py-3 bg-gray-800 text-white rounded-lg shadow hover:bg-gray-900"
                >
                    Register
                </Link>
            </div>

        </main>

        <footer class="py-6 text-gray-500 text-sm text-center">
            Track.. Thrive.. Repeat..
        </footer>

    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
const FitmateLogo = "/images/fitmate-logo.jpg";
</script>

<template>
    <Head title="Welcome" />

    <!-- Background Gradient -->
    <div class="min-h-screen w-full bg-gradient-to-br from-gray-900 via-black to-gray-800 text-white flex flex-col">

        <!-- Header -->
        <header class="flex items-center px-10 py-6">
            <img 
                :src="FitmateLogo" 
                alt="FitMate Logo" 
                class="w-20 h-auto brightness-110 contrast-125"
            />
        </header>

        <!-- Hero Section -->
        <main class="flex justify-center items-center flex-1 px-6 py-12">
            <div class="text-center max-w-3xl animate-fadeIn">

                <!-- Title -->
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6">
                    Welcome to 
                    <span class="bg-gradient-to-r from-blue-400 to-blue-600 text-transparent bg-clip-text">
                        FitMate
                    </span>
                </h1>

                <!-- Quotes -->
                <div class="space-y-5 text-lg md:text-xl text-gray-300 leading-relaxed">
                    <p>“The body achieves what the mind believes.”</p>
                    <p>“Don’t limit your challenges — challenge your limits.”</p>
                    <p>“Success starts with self-discipline.”</p>
                    <p>“Train like a beast. Look like a beauty.”</p>
                </div>

                <!-- CTA Buttons -->
                <div class="mt-10 flex justify-center gap-6">
                    <Link 
                        href="/login"
                        class="px-8 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 
                               shadow-lg shadow-blue-700/40 transition duration-300 text-lg font-semibold">
                        Log In
                    </Link>

                    <Link 
                        href="/register"
                        class="px-8 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 
                               shadow-lg shadow-gray-700/40 transition duration-300 text-lg font-semibold">
                        Register
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 text-gray-400 text-center text-sm tracking-wide">
            Track.. Thrive.. Repeat..
        </footer>

    </div>
</template>

<style>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
  animation: fadeIn 1.5s ease-out forwards;
}
</style>

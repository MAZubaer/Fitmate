<script setup>
import { Head, Link } from '@inertiajs/vue3';

const Logo = "/images/fitmate-logo-white.jpg";
const Motivational = "/images/push-yourself.png"; 
</script>

<template>
    <Head title="Welcome" />

    <!-- Background -->
    <div class="min-h-screen w-full relative 
                bg-gradient-to-br from-gray-900 via-black to-gray-800 
                text-white overflow-hidden">

        <!-- CENTER Background Watermark -->
        <img
            :src="Motivational"
            alt="Motivation"
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2
                   w-[600px] md:w-[900px] opacity-[0.06] blur-[2px]
                   select-none pointer-events-none"
        />

        <!-- Logo (Top-left) -->
        <header class="absolute top-6 left-6">
            <div 
                class="w-24 h-24 rounded-full bg-white/10 backdrop-blur-md shadow-md
                       border border-white/20 flex items-center justify-center overflow-hidden"
            >
                <img 
                    :src="Logo" 
                    alt="FitMate Logo" 
                    class="w-24 h-24 object-contain"
                />
            </div>
        </header>

        <!-- Main Hero -->
        <main class="flex justify-center items-center h-screen text-center px-6">
            <div class="max-w-3xl animate-fadeIn relative z-10">

                <h1 class="text-5xl md:text-6xl font-extrabold mb-6 tracking-tight">
                    Welcome to 
                    <span class="bg-gradient-to-r from-blue-400 to-blue-600 
                                 text-transparent bg-clip-text">
                        FitMate
                    </span>
                </h1>

                <div class="space-y-5 text-lg md:text-xl text-gray-300 leading-relaxed">
                    <p>“The body achieves what the mind believes.”</p>
                    <p>“Don’t limit your challenges — challenge your limits.”</p>
                    <p>“Success starts with self-discipline.”</p>
                    <p>“Train like a beast. Look like a beauty.”</p>
                </div>

                <!-- Buttons -->
                <div class="mt-10 flex justify-center gap-6">
                    <Link 
                        href="/login"
                        class="px-8 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 
                               shadow-lg shadow-blue-700/40 transition duration-300 
                               text-lg font-semibold"
                    >
                        Log In
                    </Link>

                    <Link 
                        href="/register"
                        class="px-8 py-3 rounded-xl bg-gray-700 hover:bg-gray-600 
                               shadow-lg shadow-gray-700/40 transition duration-300 
                               text-lg font-semibold"
                    >
                        Register
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="py-6 text-gray-400 text-center text-sm tracking-wide relative z-10">
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

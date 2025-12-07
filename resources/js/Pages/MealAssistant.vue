<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <AppLayout>
        <Head title="Meal Assistant" />
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h1 class="text-4xl font-bold text-[#1B3C53] mb-4">
                        AI Meal Assistant
                    </h1>
                    <p class="text-gray-600 text-lg">
                        AI-powered meal planning and recommendations coming soon...
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

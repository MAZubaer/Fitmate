<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import Logo from '@/Components/Logo.vue';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const highlights = [
    'Plan workouts and track every rep',
    'Log meals, water, steps and mood',
    'Get advice from an AI coach that knows your data',
];
</script>

<template>
    <div class="min-h-screen bg-canvas lg:grid lg:grid-cols-[1.05fr_1fr]">
        <!-- Brand panel -->
        <aside class="relative hidden overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute -left-24 -top-24 h-[28rem] w-[28rem] rounded-full bg-brand-500/25 blur-3xl" />
                <div class="absolute -bottom-32 right-0 h-96 w-96 rounded-full bg-lime-400/15 blur-3xl" />
                <div
                    class="absolute inset-0 opacity-[0.07]"
                    style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 44px 44px; mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%)"
                />
            </div>

            <Link href="/" class="relative w-fit"><Logo /></Link>

            <div class="relative max-w-md">
                <h2 class="text-4xl font-extrabold leading-[1.1] tracking-tight xl:text-5xl">
                    Train smarter.<br />
                    <span class="bg-gradient-to-r from-brand-300 to-lime-300 bg-clip-text text-transparent">Live stronger.</span>
                </h2>
                <p class="mt-5 text-lg text-slate-300">Everything you need to build healthy habits, in one place.</p>
                <ul class="mt-8 space-y-3.5">
                    <li v-for="item in highlights" :key="item" class="flex items-center gap-3 text-slate-200">
                        <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-brand-500/20 text-brand-300">
                            <Icon name="check" class="h-3.5 w-3.5" :stroke-width="3" />
                        </span>
                        {{ item }}
                    </li>
                </ul>
            </div>

            <p class="relative text-sm text-slate-500">Track. Thrive. Repeat.</p>
        </aside>

        <!-- Form panel -->
        <main class="flex min-h-screen flex-col items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-sm animate-fade-up">
                <Link href="/" class="mb-10 inline-flex lg:hidden"><Logo /></Link>
                <h1 v-if="title" class="text-2xl font-bold tracking-tight sm:text-3xl">{{ title }}</h1>
                <p v-if="subtitle" class="mt-2 text-sm text-muted">{{ subtitle }}</p>
                <div :class="title || subtitle ? 'mt-8' : ''">
                    <slot />
                </div>
            </div>
        </main>
    </div>
</template>

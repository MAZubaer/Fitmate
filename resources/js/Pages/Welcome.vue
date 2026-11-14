<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';
import Logo from '@/Components/Logo.vue';

const user = computed(() => usePage().props.auth?.user);

const features = [
    { icon: 'dumbbell', title: 'Workout planner', text: 'Schedule sessions, build multi-exercise plans and check them off as you go.', tone: 'from-violet-500/20 text-violet-300' },
    { icon: 'utensils', title: 'Meal diary', text: 'Log meals and calories, grow your streak and earn points for consistency.', tone: 'from-brand-500/20 text-brand-300' },
    { icon: 'activity', title: 'Health metrics', text: 'Track BMI, steps, water and daily mood — each with a clear 7-day trend.', tone: 'from-sky-500/20 text-sky-300' },
    { icon: 'sparkles', title: 'AI coach', text: 'Chat with a coach that reads your real data and gives personal advice.', tone: 'from-lime-400/20 text-lime-300' },
    { icon: 'bell', title: 'Smart reminders', text: 'Get email and in-app nudges right when a meal or workout is due.', tone: 'from-amber-500/20 text-amber-300' },
    { icon: 'download', title: 'Reports', text: 'Export your history for any period as a polished PDF or a CSV file.', tone: 'from-rose-500/20 text-rose-300' },
];

const steps = [
    { title: 'Create your account', text: 'Sign up with email or Google in under a minute.' },
    { title: 'Log your day', text: 'Add workouts, meals, water and steps as they happen.' },
    { title: 'See your progress', text: 'Follow your trends and ask your AI coach what to do next.' },
];

// Purely decorative preview bars for the hero mock-up.
const previewBars = [42, 68, 55, 80, 62, 90, 74];
const year = new Date().getFullYear();
</script>

<template>
    <Head title="Your all-in-one fitness companion" />

    <div class="min-h-screen overflow-x-hidden bg-slate-950 text-white">
        <!-- Background -->
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[900px] overflow-hidden">
            <div class="absolute left-1/2 top-[-200px] h-[600px] w-[900px] -translate-x-1/2 rounded-full bg-brand-500/20 blur-[120px]" />
            <div class="absolute right-[-100px] top-[300px] h-[400px] w-[400px] rounded-full bg-lime-400/10 blur-[100px]" />
            <div
                class="absolute inset-0 opacity-[0.06]"
                style="background-image: linear-gradient(to right, white 1px, transparent 1px), linear-gradient(to bottom, white 1px, transparent 1px); background-size: 56px 56px; mask-image: radial-gradient(ellipse 70% 60% at 50% 0%, black, transparent)"
            />
        </div>

        <!-- Nav -->
        <header class="relative z-10">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
                <Link href="/" aria-label="FitMate home"><Logo /></Link>
                <div class="hidden items-center gap-8 text-sm text-slate-300 md:flex">
                    <a href="#features" class="transition hover:text-white">Features</a>
                    <a href="#how" class="transition hover:text-white">How it works</a>
                </div>
                <div class="flex items-center gap-2">
                    <template v-if="user">
                        <Link href="/dashboard" class="btn bg-white text-slate-950 hover:bg-slate-200">
                            Open dashboard <Icon name="arrow-right" class="h-4 w-4" />
                        </Link>
                    </template>
                    <template v-else>
                        <Link href="/login" class="btn text-slate-300 hover:bg-white/10 hover:text-white">Log in</Link>
                        <Link href="/register" class="btn bg-white text-slate-950 hover:bg-slate-200">Get started</Link>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Hero -->
        <section class="relative z-10 mx-auto max-w-7xl px-5 pb-20 pt-12 text-center sm:px-8 sm:pt-20">
            <div class="animate-fade-up">
                <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3.5 py-1.5 text-xs font-medium text-slate-300 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-400" /> Now with a personal AI coach
                </span>
                <h1 class="mx-auto mt-6 max-w-4xl text-4xl font-extrabold leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl">
                    Your fitness,
                    <span class="bg-gradient-to-r from-brand-300 via-emerald-300 to-lime-300 bg-clip-text text-transparent">all in one place.</span>
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg text-slate-400 sm:text-xl">
                    Plan workouts, log meals, track BMI, steps, water and mood — then let an AI coach turn your data into a plan.
                </p>
                <div class="mt-10 flex flex-col justify-center gap-3 sm:flex-row">
                    <Link :href="user ? '/dashboard' : '/register'" class="btn bg-brand-500 px-6 py-3.5 text-base text-slate-950 shadow-lg shadow-brand-500/25 hover:bg-brand-400">
                        {{ user ? 'Go to dashboard' : 'Start for free' }} <Icon name="arrow-right" class="h-4 w-4" />
                    </Link>
                    <a href="#features" class="btn border border-white/15 px-6 py-3.5 text-base text-white hover:bg-white/10">See features</a>
                </div>
            </div>

            <!-- Product preview (illustrative) -->
            <div class="relative mx-auto mt-16 max-w-5xl animate-fade-up [animation-delay:150ms] sm:mt-20" aria-hidden="true">
                <div class="absolute -inset-4 rounded-[2rem] bg-gradient-to-b from-brand-500/20 to-transparent blur-2xl" />
                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/80 p-3 shadow-2xl backdrop-blur sm:p-4">
                    <div class="mb-3 flex items-center gap-1.5 px-1">
                        <span class="h-2.5 w-2.5 rounded-full bg-white/15" /><span class="h-2.5 w-2.5 rounded-full bg-white/15" /><span class="h-2.5 w-2.5 rounded-full bg-white/15" />
                    </div>
                    <div class="grid gap-3 text-left sm:grid-cols-3">
                        <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/5">
                            <p class="text-xs text-slate-400">Calories today</p>
                            <p class="mt-1 text-2xl font-bold">1,640 <span class="text-sm font-medium text-slate-500">/ 2,200</span></p>
                            <div class="mt-3 h-1.5 rounded-full bg-white/10"><div class="h-full w-3/4 rounded-full bg-brand-400" /></div>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/5">
                            <p class="text-xs text-slate-400">Steps</p>
                            <p class="mt-1 text-2xl font-bold">8,412</p>
                            <div class="mt-3 h-1.5 rounded-full bg-white/10"><div class="h-full w-[84%] rounded-full bg-amber-400" /></div>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/5">
                            <p class="text-xs text-slate-400">Water</p>
                            <p class="mt-1 text-2xl font-bold">1.8 <span class="text-sm font-medium text-slate-500">L</span></p>
                            <div class="mt-3 h-1.5 rounded-full bg-white/10"><div class="h-full w-[72%] rounded-full bg-sky-400" /></div>
                        </div>
                        <div class="rounded-2xl bg-white/5 p-4 ring-1 ring-white/5 sm:col-span-2">
                            <p class="text-xs text-slate-400">Energy balance · 7 days</p>
                            <div class="mt-4 flex h-32 items-end gap-2 sm:gap-3">
                                <div v-for="(h, i) in previewBars" :key="i" class="flex h-full flex-1 items-end gap-1">
                                    <div class="flex-1 rounded-t-md bg-brand-400/90" :style="{ height: h + '%' }" />
                                    <div class="flex-1 rounded-t-md bg-orange-400/80" :style="{ height: h * 0.55 + '%' }" />
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 rounded-2xl bg-white/5 p-4 ring-1 ring-white/5">
                            <p class="flex items-center gap-1.5 text-xs text-slate-400"><Icon name="sparkles" class="h-3.5 w-3.5 text-brand-300" /> AI Coach</p>
                            <p class="ml-auto max-w-[85%] rounded-xl rounded-tr-sm bg-brand-500 px-3 py-2 text-xs text-slate-950">What should I train today?</p>
                            <p class="max-w-[90%] rounded-xl rounded-tl-sm bg-white/10 px-3 py-2 text-xs text-slate-200">You hit legs twice this week — try an upper-body push day with 4×10 bench press.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="features" class="relative z-10 mx-auto max-w-7xl scroll-mt-8 px-5 py-20 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-400">Features</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Everything you need to stay on track</h2>
                <p class="mt-4 text-slate-400">One app for training, nutrition and recovery — no spreadsheets required.</p>
            </div>
            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="f in features"
                    :key="f.title"
                    class="group rounded-3xl border border-white/10 bg-white/[0.03] p-6 transition hover:-translate-y-1 hover:border-white/20 hover:bg-white/[0.05]"
                >
                    <span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br to-transparent ring-1 ring-white/10" :class="f.tone">
                        <Icon :name="f.icon" />
                    </span>
                    <h3 class="mt-5 text-lg font-semibold">{{ f.title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ f.text }}</p>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how" class="relative z-10 mx-auto max-w-7xl scroll-mt-8 px-5 py-20 sm:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-wider text-brand-400">How it works</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Three steps to a healthier routine</h2>
            </div>
            <ol class="mt-14 grid gap-6 md:grid-cols-3">
                <li v-for="(s, i) in steps" :key="s.title" class="relative rounded-3xl border border-white/10 p-6">
                    <span class="text-5xl font-extrabold text-white/10">0{{ i + 1 }}</span>
                    <h3 class="mt-2 text-lg font-semibold">{{ s.title }}</h3>
                    <p class="mt-2 text-sm text-slate-400">{{ s.text }}</p>
                </li>
            </ol>
        </section>

        <!-- CTA -->
        <section class="relative z-10 mx-auto max-w-7xl px-5 py-20 sm:px-8">
            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-500 to-emerald-700 px-6 py-14 text-center sm:px-12 sm:py-20">
                <div class="pointer-events-none absolute -right-16 -top-16 h-64 w-64 rounded-full bg-lime-300/30 blur-3xl" />
                <h2 class="relative text-3xl font-bold tracking-tight text-white sm:text-4xl">Ready to make progress you can see?</h2>
                <p class="relative mx-auto mt-4 max-w-xl text-brand-50/90">Join FitMate and turn everyday effort into lasting habits.</p>
                <Link :href="user ? '/dashboard' : '/register'" class="btn relative mt-8 bg-slate-950 px-6 py-3.5 text-base text-white hover:bg-slate-900">
                    {{ user ? 'Open your dashboard' : 'Create your free account' }} <Icon name="arrow-right" class="h-4 w-4" />
                </Link>
            </div>
        </section>

        <footer class="relative z-10 border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-5 py-8 text-sm text-slate-500 sm:flex-row sm:px-8">
                <Logo />
                <p>Track. Thrive. Repeat.</p>
                <p>© {{ year }} FitMate</p>
            </div>
        </footer>
    </div>
</template>

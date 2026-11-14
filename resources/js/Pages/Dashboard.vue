<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChartCanvas from '@/Components/ChartCanvas.vue';
import Icon from '@/Components/Icon.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';
import { toast } from '@/composables/useToast';
import { formatNumber, formatTime, greeting, lastNDays, relativeDay, weekday } from '@/utils/format';

const props = defineProps({
    user: Object,
    stats: Object,
    calorieChart: Array,
    calorieBurnedChart: Array,
});

const STEP_TARGET = 10000;
const WATER_TARGET = 2500;

const stats = ref({ ...props.stats });
const displayName = computed(() => props.user.nickname || props.user.name?.split(' ')[0] || 'there');
const todayLong = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });

/* ---------------- Live data ---------------- */
const todaySteps = ref(0);
const todayWater = ref(0);
const todayMood = ref(null);
const stepHistory = ref([]);
const waterHistory = ref([]);
const bmiHistory = ref([]);
const upcoming = ref([]);
const upcomingLoading = ref(true);
const weather = ref(null);

const fetchSteps = async () => {
    const [today, history] = await Promise.allSettled([axios.get('/steps/today'), axios.get('/steps-history')]);
    if (today.status === 'fulfilled') todaySteps.value = Number(today.value.data.steps) || 0;
    if (history.status === 'fulfilled') stepHistory.value = history.value.data.history ?? [];
};

const fetchWater = async () => {
    const [today, history] = await Promise.allSettled([axios.get('/water/today'), axios.get('/water-history')]);
    if (today.status === 'fulfilled') todayWater.value = Number(today.value.data.amount) || 0;
    if (history.status === 'fulfilled') waterHistory.value = history.value.data.history ?? [];
};

const fetchBmi = async () => {
    try {
        bmiHistory.value = (await axios.get('/bmi-history')).data.history ?? [];
    } catch {
        bmiHistory.value = [];
    }
};

const fetchMood = async () => {
    try {
        todayMood.value = (await axios.get('/mood/today')).data.mood;
    } catch {
        todayMood.value = null;
    }
};

const fetchUpcoming = async () => {
    try {
        upcoming.value = (await axios.get('/recent-activity')).data.workouts ?? [];
    } catch {
        upcoming.value = [];
    } finally {
        upcomingLoading.value = false;
    }
};

const weatherCodes = {
    0: ['Clear sky', '☀️'], 1: ['Mainly clear', '🌤️'], 2: ['Partly cloudy', '⛅'], 3: ['Overcast', '☁️'],
    45: ['Fog', '🌫️'], 48: ['Rime fog', '🌫️'], 51: ['Light drizzle', '🌦️'], 53: ['Drizzle', '🌦️'], 55: ['Heavy drizzle', '🌦️'],
    56: ['Freezing drizzle', '🌧️'], 57: ['Freezing drizzle', '🌧️'], 61: ['Light rain', '🌧️'], 63: ['Rain', '🌧️'], 65: ['Heavy rain', '🌧️'],
    66: ['Freezing rain', '🌧️'], 67: ['Freezing rain', '🌧️'], 71: ['Light snow', '❄️'], 73: ['Snow', '❄️'], 75: ['Heavy snow', '❄️'],
    77: ['Snow grains', '❄️'], 80: ['Showers', '🌦️'], 81: ['Showers', '🌦️'], 82: ['Heavy showers', '⛈️'], 85: ['Snow showers', '🌨️'],
    86: ['Snow showers', '🌨️'], 95: ['Thunderstorm', '⛈️'], 96: ['Thunderstorm', '⛈️'], 99: ['Thunderstorm', '⛈️'],
};

const fetchWeather = async () => {
    const url =
        'https://api.open-meteo.com/v1/forecast?latitude=23.8103&longitude=90.4125&current_weather=true&hourly=relative_humidity_2m&timezone=Asia/Dhaka';
    try {
        const { data } = await axios.get(url, { withCredentials: false });
        const current = data.current_weather;
        // current.time can be at 15-minute resolution ("…T19:15"); match the hourly slot by hour.
        const hour = String(current.time).slice(0, 13);
        const idx = data.hourly?.time?.findIndex((t) => String(t).startsWith(hour)) ?? -1;
        const [status, icon] = weatherCodes[current.weathercode] ?? ['Unknown', '🌡️'];
        weather.value = {
            temp: Math.round(current.temperature),
            wind: Math.round(current.windspeed),
            humidity: idx >= 0 ? data.hourly.relative_humidity_2m[idx] : null,
            status,
            icon,
        };
    } catch {
        weather.value = { temp: null, status: 'Unavailable', icon: '🌡️', humidity: null, wind: null };
    }
};

onMounted(() => {
    Promise.allSettled([fetchSteps(), fetchWater(), fetchBmi(), fetchMood(), fetchUpcoming(), fetchWeather()]);
});

/* ---------------- Derived values ---------------- */
const days = lastNDays(7);
const dayLabels = days.map(weekday);

const seriesFor = (history, field, missing = 0) => {
    const map = Object.fromEntries(history.map((h) => [String(h.date).slice(0, 10), Number(h[field])]));
    return days.map((key) => map[key] ?? missing);
};

const average = (values) => {
    const nonZero = values.filter((v) => v);
    return nonZero.length ? Math.round(nonZero.reduce((a, b) => a + b, 0) / nonZero.length) : 0;
};

const energyLabels = computed(() => props.calorieChart.map((d) => weekday(d.date)));
const energyDatasets = computed(() => [
    { label: 'Eaten', data: props.calorieChart.map((d) => d.calories), color: '#10b981' },
    { label: 'Burned', data: props.calorieBurnedChart.map((d) => d.calories), color: '#f97316' },
]);
const burnedThisWeek = computed(() => props.calorieBurnedChart.reduce((sum, d) => sum + Number(d.calories || 0), 0));

const stepSeries = computed(() => seriesFor(stepHistory.value, 'steps'));
const waterSeries = computed(() => seriesFor(waterHistory.value, 'amount'));
const bmiSeries = computed(() => seriesFor(bmiHistory.value, 'bmi', null));

const stepsDatasets = computed(() => [{ label: 'Steps', data: stepSeries.value, color: '#f59e0b' }]);
const waterDatasets = computed(() => [{ label: 'Water', data: waterSeries.value, color: '#0ea5e9' }]);
const bmiDatasets = computed(() => [{ label: 'BMI', data: bmiSeries.value, color: '#8b5cf6' }]);

const goalPercent = computed(() =>
    stats.value.daily_goal > 0 ? Math.round((stats.value.today_calories / stats.value.daily_goal) * 100) : 0,
);
const ringDash = computed(() => `${(Math.min(goalPercent.value, 100) / 100) * 97.4} 97.4`);
const caloriesLeft = computed(() => Math.max(0, stats.value.daily_goal - stats.value.today_calories));

const heroMessage = computed(() => {
    if (!stats.value.today_calories) return 'Log your first meal today to start tracking your calories.';
    if (goalPercent.value < 100) return `${formatNumber(caloriesLeft.value)} kcal left to reach today's goal. Keep it up!`;
    return "You've hit today's calorie goal. Nice work!";
});

/* ---------------- BMI ---------------- */
const bmiCategories = [
    {
        max: 18.5, label: 'Underweight', badge: 'badge-warn',
        messages: [
            'Your body may need a little extra fuel. Small, balanced meals and strength training can help you feel stronger every day.',
            'Health is about nourishment and balance. Focus on eating well and caring for your body.',
            'You’re on a journey—building strength and energy is a great next step.',
        ],
    },
    {
        max: 25, label: 'Healthy', badge: 'badge-brand',
        messages: [
            'Great job! Your BMI is within a healthy range—keep maintaining your balanced lifestyle.',
            'Consistency is your superpower. Keep eating well, staying active, and prioritizing rest.',
            'You’re doing well! Small healthy choices every day make a big difference.',
        ],
    },
    {
        max: 30, label: 'Overweight', badge: 'badge-warn',
        messages: [
            'You’re taking a positive step by checking your health. Small changes can lead to big results.',
            'Progress doesn’t have to be fast—focus on consistency, not perfection.',
            'A balanced diet and regular movement can help you feel more energetic and confident.',
        ],
    },
    {
        max: Infinity, label: 'Obese', badge: 'badge-danger',
        messages: [
            'Your health matters, and it’s never too late to start making positive changes.',
            'Every small step counts. Focus on building healthy habits one day at a time.',
            'You’re not alone—support, consistency, and patience can lead to meaningful progress.',
        ],
    },
];
const bmiCategory = (value) => (value === null || Number.isNaN(value) ? null : bmiCategories.find((c) => value < c.max));

const latestBmi = computed(() => (bmiHistory.value.length ? Number(bmiHistory.value[bmiHistory.value.length - 1].bmi) : null));

const showBmi = ref(false);
const heightCm = ref('');
const weightKg = ref('');
const bmiError = ref('');
const bmiSaving = ref(false);

const bmiPreview = computed(() => {
    const h = Number(heightCm.value) / 100;
    const w = Number(weightKg.value);
    if (!h || !w || h < 0.5 || h > 3 || w < 10 || w > 500) return null;
    return Math.round((w / (h * h)) * 100) / 100;
});
const previewCategory = computed(() => bmiCategory(bmiPreview.value));
const previewMessage = computed(() => {
    const c = previewCategory.value;
    return c ? c.messages[Math.floor(bmiPreview.value * 10) % c.messages.length] : '';
});

const openBmi = () => {
    bmiError.value = '';
    showBmi.value = true;
};

async function submitBmi() {
    bmiError.value = '';
    if (bmiPreview.value === null) {
        bmiError.value = 'Enter a height between 50–300 cm and a weight between 10–500 kg.';
        return;
    }
    bmiSaving.value = true;
    try {
        await axios.post('/bmi', { height: Number(heightCm.value) / 100, weight: Number(weightKg.value), bmi: bmiPreview.value });
        showBmi.value = false;
        toast(`BMI saved: ${bmiPreview.value}`);
        heightCm.value = '';
        weightKg.value = '';
        await fetchBmi();
    } catch (e) {
        bmiError.value = e.response?.data?.error || e.response?.data?.message || 'Could not save your BMI. Please try again.';
    } finally {
        bmiSaving.value = false;
    }
}

/* ---------------- Steps ---------------- */
const showSteps = ref(false);
const stepsInput = ref('');
const stepsError = ref('');
const stepsSaving = ref(false);

async function submitSteps() {
    stepsError.value = '';
    const steps = parseInt(stepsInput.value, 10);
    if (!steps || steps < 1) {
        stepsError.value = 'Enter a valid step count.';
        return;
    }
    stepsSaving.value = true;
    try {
        const { data } = await axios.post('/steps', { steps });
        todaySteps.value = Number(data.steps) || todaySteps.value + steps;
        stepsInput.value = '';
        showSteps.value = false;
        toast(`Added ${formatNumber(steps)} steps`);
        fetchSteps();
    } catch {
        stepsError.value = 'Could not save your steps. Please try again.';
    } finally {
        stepsSaving.value = false;
    }
}

/* ---------------- Water ---------------- */
const showWater = ref(false);
const waterInput = ref('');
const waterError = ref('');
const waterSaving = ref(false);

async function addWater(amountValue) {
    waterError.value = '';
    const amount = parseInt(amountValue, 10);
    if (!amount || amount < 1) {
        waterError.value = 'Enter a valid amount in ml.';
        return;
    }
    waterSaving.value = true;
    try {
        const { data } = await axios.post('/water', { amount });
        todayWater.value = Number(data.amount) || todayWater.value + amount;
        waterInput.value = '';
        showWater.value = false;
        toast(`Added ${amount} ml of water`);
        fetchWater();
    } catch {
        waterError.value = 'Could not save your water intake. Please try again.';
    } finally {
        waterSaving.value = false;
    }
}

/* ---------------- Calorie goal ---------------- */
const showGoal = ref(false);
const goalInput = ref(stats.value.daily_goal);
const goalError = ref('');
const goalSaving = ref(false);
const goalPresets = [1800, 2000, 2200, 2500, 3000];

async function saveGoal() {
    goalError.value = '';
    goalSaving.value = true;
    try {
        const { data } = await axios.post('/calorie-goal', { calorie_goal: Number(goalInput.value) });
        stats.value.daily_goal = data.goal;
        showGoal.value = false;
        toast('Daily calorie goal updated');
    } catch (e) {
        goalError.value = e.response?.data?.errors?.calorie_goal?.[0] || 'Could not save your goal.';
    } finally {
        goalSaving.value = false;
    }
}

/* ---------------- Mood ---------------- */
const moods = [
    { value: 'HAPPY', emoji: '😄', label: 'Great' },
    { value: 'NEUTRAL', emoji: '😐', label: 'Okay' },
    { value: 'STRESSED', emoji: '😣', label: 'Stressed' },
];

async function saveMood(mood) {
    const previous = todayMood.value;
    todayMood.value = mood;
    try {
        await axios.post('/mood', { mood });
    } catch {
        todayMood.value = previous;
        toast('Could not save your mood', 'error');
    }
}

const genderLabel = computed(() => ({ male: 'Male', female: 'Female' })[props.user.gender] ?? '—');
</script>

<template>
    <AppLayout>
        <Head title="Dashboard" />

        <!-- Hero + weather -->
        <div class="grid gap-4 lg:grid-cols-3 lg:gap-6">
            <section class="relative overflow-hidden rounded-3xl bg-slate-950 p-6 text-white shadow-lift ring-1 ring-transparent dark:bg-gradient-to-br dark:from-slate-900 dark:to-slate-950 dark:ring-white/10 sm:p-8 lg:col-span-2">
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl" />
                <div class="pointer-events-none absolute -bottom-28 left-1/3 h-64 w-64 rounded-full bg-lime-400/10 blur-3xl" />

                <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm text-slate-400">{{ todayLong }}</p>
                        <h1 class="mt-1 text-2xl font-bold tracking-tight sm:text-3xl">{{ greeting() }}, {{ displayName }} 👋</h1>
                        <p class="mt-2 max-w-md text-sm text-slate-300">{{ heroMessage }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-500/15 px-3 py-1.5 text-sm font-semibold text-orange-300 ring-1 ring-orange-400/20" title="Meal logging streak">
                            <Icon name="flame" class="h-4 w-4" /> {{ stats.meal_streak }} day{{ stats.meal_streak === 1 ? '' : 's' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/15 px-3 py-1.5 text-sm font-semibold text-amber-300 ring-1 ring-amber-300/20" title="Meal points">
                            <Icon name="star" class="h-4 w-4" /> {{ formatNumber(stats.meal_points) }} pts
                        </span>
                    </div>
                </div>

                <div class="relative mt-7 flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">How are you feeling today?</p>
                        <div class="mt-2 inline-flex gap-1 rounded-2xl bg-white/5 p-1 ring-1 ring-white/10">
                            <button
                                v-for="m in moods"
                                :key="m.value"
                                type="button"
                                class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-medium transition"
                                :class="todayMood === m.value ? 'bg-white text-slate-900 shadow' : 'text-slate-300 hover:bg-white/10 hover:text-white'"
                                :aria-pressed="todayMood === m.value"
                                @click="saveMood(m.value)"
                            >
                                <span class="text-lg leading-none">{{ m.emoji }}</span> {{ m.label }}
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <Link href="/workout" class="btn bg-brand-500 text-slate-950 hover:bg-brand-400">
                            <Icon name="dumbbell" class="h-4 w-4" /> Log workout
                        </Link>
                        <Link href="/meals/create" class="btn bg-white/10 text-white ring-1 ring-white/15 hover:bg-white/15">
                            <Icon name="utensils" class="h-4 w-4" /> Track meal
                        </Link>
                        <Link href="/meal-assistant" class="btn bg-white/10 text-white ring-1 ring-white/15 hover:bg-white/15">
                            <Icon name="sparkles" class="h-4 w-4" /> Ask coach
                        </Link>
                    </div>
                </div>
            </section>

            <section class="card card-pad flex flex-col justify-between">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="eyebrow">Weather · Dhaka</p>
                        <template v-if="weather">
                            <p class="mt-3 text-5xl font-bold tracking-tight tabular-nums">
                                {{ weather.temp ?? '—' }}<span v-if="weather.temp !== null" class="text-3xl text-muted">°C</span>
                            </p>
                            <p class="mt-1 text-sm text-muted">{{ weather.status }}</p>
                        </template>
                        <template v-else>
                            <div class="skeleton mt-3 h-12 w-24" />
                            <div class="skeleton mt-2 h-4 w-20" />
                        </template>
                    </div>
                    <span class="text-5xl leading-none">{{ weather?.icon ?? '' }}</span>
                </div>
                <dl class="mt-6 grid grid-cols-3 gap-2 border-t border-line pt-4 text-center">
                    <div>
                        <dt class="text-xs text-muted">Humidity</dt>
                        <dd class="mt-0.5 font-semibold tabular-nums">{{ weather?.humidity != null ? weather.humidity + '%' : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted">Age</dt>
                        <dd class="mt-0.5 font-semibold">{{ user.age || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted">Gender</dt>
                        <dd class="mt-0.5 font-semibold">{{ genderLabel }}</dd>
                    </div>
                </dl>
                <p class="mt-3 text-center text-xs text-muted">Member since {{ stats.joined_date }}</p>
            </section>
        </div>

        <!-- KPIs -->
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:mt-6 lg:gap-6 xl:grid-cols-4">
            <div class="card card-pad flex items-center gap-5">
                <div class="relative h-20 w-20 shrink-0">
                    <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
                        <circle cx="18" cy="18" r="15.5" fill="none" stroke-width="3.5" class="stroke-subtle" />
                        <circle
                            cx="18" cy="18" r="15.5" fill="none" stroke-width="3.5" stroke-linecap="round"
                            class="stroke-brand-500 transition-all duration-700"
                            :stroke-dasharray="ringDash"
                        />
                    </svg>
                    <span class="absolute inset-0 grid place-items-center text-sm font-bold tabular-nums">{{ goalPercent }}%</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-muted">Calories today</p>
                    <p class="mt-1 text-2xl font-bold tracking-tight tabular-nums">
                        {{ formatNumber(stats.today_calories) }}
                        <span class="text-sm font-medium text-muted">/ {{ formatNumber(stats.daily_goal) }}</span>
                    </p>
                    <button
                        type="button"
                        class="mt-1.5 inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300"
                        @click="goalInput = stats.daily_goal; goalError = ''; showGoal = true"
                    >
                        <Icon name="target" class="h-3.5 w-3.5" /> Edit goal
                    </button>
                </div>
            </div>

            <StatCard
                label="Burned this week"
                :value="formatNumber(burnedThisWeek)"
                unit="kcal"
                icon="flame"
                tone="orange"
                hint="From completed workouts, last 7 days"
            />

            <StatCard
                label="Steps today"
                :value="formatNumber(todaySteps)"
                icon="footprints"
                tone="amber"
                :progress="(todaySteps / STEP_TARGET) * 100"
                :hint="`Daily target ${formatNumber(STEP_TARGET)}`"
            >
                <template #action>
                    <button class="btn-icon h-8 w-8 bg-subtle" aria-label="Log steps" title="Log steps" @click="stepsError = ''; showSteps = true">
                        <Icon name="plus" class="h-4 w-4" />
                    </button>
                </template>
            </StatCard>

            <StatCard
                label="Water today"
                :value="formatNumber(todayWater)"
                unit="ml"
                icon="droplet"
                tone="sky"
                :progress="(todayWater / WATER_TARGET) * 100"
                :hint="`Daily target ${formatNumber(WATER_TARGET)} ml`"
            >
                <template #action>
                    <button class="btn-icon h-8 w-8 bg-subtle" aria-label="Log water" title="Log water" @click="waterError = ''; showWater = true">
                        <Icon name="plus" class="h-4 w-4" />
                    </button>
                </template>
            </StatCard>
        </div>

        <!-- Energy balance + upcoming -->
        <div class="mt-4 grid gap-4 lg:mt-6 lg:grid-cols-3 lg:gap-6">
            <section class="card card-pad lg:col-span-2">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="card-title">Energy balance</h2>
                        <p class="mt-0.5 text-sm text-muted">Calories eaten vs. burned over the last 7 days</p>
                    </div>
                    <div class="flex gap-6 text-sm">
                        <div>
                            <p class="text-muted">Eaten</p>
                            <p class="font-semibold tabular-nums">{{ formatNumber(stats.total_calories) }} kcal</p>
                        </div>
                        <div>
                            <p class="text-muted">Goal hit</p>
                            <p class="font-semibold tabular-nums">{{ stats.goals_completed }}/7 days</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 h-64">
                    <ChartCanvas :labels="energyLabels" :datasets="energyDatasets" unit="kcal" empty-text="Log meals and complete workouts to see your trend" />
                </div>
            </section>

            <section class="card card-pad flex flex-col">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="card-title">Up next</h2>
                        <p class="mt-0.5 text-sm text-muted">Today & tomorrow</p>
                    </div>
                    <Link href="/workout" class="inline-flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        View all <Icon name="chevron-right" class="h-4 w-4" />
                    </Link>
                </div>

                <div class="scrollbar-thin -mx-2 mt-4 max-h-72 flex-1 space-y-1 overflow-y-auto px-2">
                    <template v-if="upcomingLoading">
                        <div v-for="i in 3" :key="i" class="flex items-center gap-3 py-2">
                            <div class="skeleton h-10 w-10 rounded-xl" />
                            <div class="flex-1 space-y-2"><div class="skeleton h-3.5 w-2/3" /><div class="skeleton h-3 w-1/3" /></div>
                        </div>
                    </template>
                    <div v-else-if="upcoming.length === 0" class="flex h-full flex-col items-center justify-center py-8 text-center">
                        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-subtle text-muted"><Icon name="calendar" /></div>
                        <p class="mt-3 text-sm font-medium">Nothing scheduled</p>
                        <p class="mt-0.5 text-xs text-muted">Plan a workout to see it here.</p>
                        <Link href="/workout" class="btn-secondary btn-sm mt-4"><Icon name="plus" class="h-3.5 w-3.5" /> Plan workout</Link>
                    </div>
                    <template v-else>
                    <div v-for="w in upcoming" :key="w.id" class="flex items-center gap-3 rounded-xl p-2 transition hover:bg-subtle">
                        <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-500/10 text-brand-600 dark:text-brand-400">
                            <Icon :name="w.completed == 1 || w.completed === true ? 'check' : 'dumbbell'" class="h-[18px] w-[18px]" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold" :class="{ 'text-muted line-through': w.completed == 1 || w.completed === true }">{{ w.name }}</p>
                            <p class="text-xs text-muted">{{ w.sets }} × {{ w.reps }} · {{ w.calories }} kcal</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold">{{ formatTime(w.time) }}</p>
                            <p class="text-xs text-muted">{{ relativeDay(w.date) }}</p>
                        </div>
                    </div>
                    </template>
                </div>
            </section>
        </div>

        <!-- Health trends -->
        <div class="mt-4 grid gap-4 md:grid-cols-2 lg:mt-6 lg:gap-6 xl:grid-cols-3">
            <section class="card card-pad">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="card-title">Steps</h2>
                        <p class="mt-0.5 text-sm text-muted">7-day average · {{ formatNumber(average(stepSeries)) }}</p>
                    </div>
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400"><Icon name="footprints" class="h-[18px] w-[18px]" /></span>
                </div>
                <div class="mt-4 h-44"><ChartCanvas :labels="dayLabels" :datasets="stepsDatasets" unit="steps" /></div>
            </section>

            <section class="card card-pad">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="card-title">Hydration</h2>
                        <p class="mt-0.5 text-sm text-muted">7-day average · {{ formatNumber(average(waterSeries)) }} ml</p>
                    </div>
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-sky-500/10 text-sky-600 dark:text-sky-400"><Icon name="droplet" class="h-[18px] w-[18px]" /></span>
                </div>
                <div class="mt-4 h-44"><ChartCanvas :labels="dayLabels" :datasets="waterDatasets" unit="ml" /></div>
            </section>

            <section class="card card-pad md:col-span-2 xl:col-span-1">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="card-title">BMI</h2>
                        <p class="mt-0.5 flex items-center gap-2 text-sm text-muted">
                            <template v-if="latestBmi !== null">
                                Latest · <span class="font-semibold text-ink">{{ latestBmi.toFixed(1) }}</span>
                                <span :class="bmiCategory(latestBmi)?.badge">{{ bmiCategory(latestBmi)?.label }}</span>
                            </template>
                            <template v-else>Not recorded yet</template>
                        </p>
                    </div>
                    <button class="btn-secondary btn-sm" @click="openBmi"><Icon name="scale" class="h-3.5 w-3.5" /> Update</button>
                </div>
                <div class="mt-4 h-44"><ChartCanvas type="line" :labels="dayLabels" :datasets="bmiDatasets" :begin-at-zero="false" empty-text="Add your BMI to start tracking" /></div>
            </section>
        </div>

        <!-- BMI modal -->
        <Modal :show="showBmi" title="Update BMI" description="You can record your BMI once per day." max-width="md" @close="showBmi = false">
            <form class="space-y-4" @submit.prevent="submitBmi">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="bmi-height">Height</label>
                        <div class="relative">
                            <input id="bmi-height" v-model="heightCm" type="number" inputmode="decimal" step="0.1" min="50" max="300" class="input pr-12" placeholder="170" required />
                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-muted">cm</span>
                        </div>
                    </div>
                    <div>
                        <label class="label" for="bmi-weight">Weight</label>
                        <div class="relative">
                            <input id="bmi-weight" v-model="weightKg" type="number" inputmode="decimal" step="0.1" min="10" max="500" class="input pr-12" placeholder="68" required />
                            <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-muted">kg</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-subtle p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted">Your BMI</p>
                        <span v-if="previewCategory" :class="previewCategory.badge">{{ previewCategory.label }}</span>
                    </div>
                    <p class="mt-1 text-3xl font-bold tabular-nums">{{ bmiPreview !== null ? bmiPreview.toFixed(1) : '—' }}</p>
                    <p v-if="previewMessage" class="mt-2 text-sm text-muted">{{ previewMessage }}</p>
                </div>

                <p v-if="bmiError" class="field-error">{{ bmiError }}</p>
                <button type="submit" class="btn-primary w-full" :disabled="bmiSaving">{{ bmiSaving ? 'Saving…' : 'Save BMI' }}</button>
            </form>
        </Modal>

        <!-- Steps modal -->
        <Modal :show="showSteps" title="Log steps" description="Steps are added to today's total." max-width="sm" @close="showSteps = false">
            <form class="space-y-4" @submit.prevent="submitSteps">
                <input v-model="stepsInput" type="number" inputmode="numeric" min="1" class="input text-lg" placeholder="e.g. 3500" required />
                <div class="flex flex-wrap gap-2">
                    <button v-for="n in [1000, 2500, 5000]" :key="n" type="button" class="btn-secondary btn-sm" @click="stepsInput = n">
                        {{ formatNumber(n) }}
                    </button>
                </div>
                <p v-if="stepsError" class="field-error">{{ stepsError }}</p>
                <button type="submit" class="btn-primary w-full" :disabled="stepsSaving">{{ stepsSaving ? 'Saving…' : 'Add steps' }}</button>
            </form>
        </Modal>

        <!-- Water modal -->
        <Modal :show="showWater" title="Log water" description="Tap a quick amount or enter your own." max-width="sm" @close="showWater = false">
            <div class="grid grid-cols-3 gap-2">
                <button
                    v-for="n in [250, 500, 750]"
                    :key="n"
                    type="button"
                    class="flex flex-col items-center gap-1 rounded-2xl border border-line p-3 transition hover:border-sky-400 hover:bg-sky-500/5 disabled:opacity-50"
                    :disabled="waterSaving"
                    @click="addWater(n)"
                >
                    <Icon name="droplet" class="h-5 w-5 text-sky-500" />
                    <span class="text-sm font-semibold">{{ n }} ml</span>
                </button>
            </div>
            <form class="mt-4 flex gap-2" @submit.prevent="addWater(waterInput)">
                <input v-model="waterInput" type="number" inputmode="numeric" min="1" class="input" placeholder="Custom amount (ml)" />
                <button type="submit" class="btn-primary" :disabled="waterSaving">Add</button>
            </form>
            <p v-if="waterError" class="field-error">{{ waterError }}</p>
        </Modal>

        <!-- Goal modal -->
        <Modal :show="showGoal" title="Daily calorie goal" description="Used for your progress ring and weekly stats." max-width="sm" @close="showGoal = false">
            <form class="space-y-4" @submit.prevent="saveGoal">
                <div class="relative">
                    <input v-model="goalInput" type="number" inputmode="numeric" min="500" max="10000" class="input pr-14 text-lg" required />
                    <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-muted">kcal</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="p in goalPresets"
                        :key="p"
                        type="button"
                        :class="Number(goalInput) === p ? 'btn btn-sm bg-brand-500/10 text-brand-700 ring-1 ring-brand-500/40 dark:text-brand-300' : 'btn-secondary btn-sm'"
                        @click="goalInput = p"
                    >
                        {{ formatNumber(p) }}
                    </button>
                </div>
                <p v-if="goalError" class="field-error">{{ goalError }}</p>
                <button type="submit" class="btn-primary w-full" :disabled="goalSaving">{{ goalSaving ? 'Saving…' : 'Save goal' }}</button>
            </form>
        </Modal>
    </AppLayout>
</template>

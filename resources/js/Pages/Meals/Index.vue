<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { formatNumber, formatTime, relativeDay, todayKey } from '@/utils/format';

const props = defineProps({
    meals: Array,
});

const user = computed(() => usePage().props.auth.user ?? {});
const search = ref('');

const mealType = (time) => {
    if (!time) return null;
    const hour = Number(String(time).split(':')[0]);
    if (hour < 11) return { label: 'Breakfast', emoji: '🥣' };
    if (hour < 16) return { label: 'Lunch', emoji: '🥗' };
    if (hour < 18) return { label: 'Snack', emoji: '🍎' };
    return { label: 'Dinner', emoji: '🍲' };
};

const filtered = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.meals;
    return props.meals.filter((m) => m.name.toLowerCase().includes(q) || (m.description ?? '').toLowerCase().includes(q));
});

// Group meals by day (newest first), meals within a day ordered by time.
const groups = computed(() => {
    const byDay = {};
    filtered.value.forEach((meal) => {
        const key = meal.meal_date ? String(meal.meal_date).slice(0, 10) : 'undated';
        (byDay[key] ||= []).push(meal);
    });
    return Object.entries(byDay)
        .sort(([a], [b]) => b.localeCompare(a))
        .map(([date, meals]) => ({
            date,
            meals: meals.sort((a, b) => String(a.meal_time ?? '').localeCompare(String(b.meal_time ?? ''))),
            total: meals.reduce((sum, m) => sum + Number(m.calories || 0), 0),
        }));
});

const todayMeals = computed(() => props.meals.filter((m) => String(m.meal_date).slice(0, 10) === todayKey()));
const todayCalories = computed(() => todayMeals.value.reduce((sum, m) => sum + Number(m.calories || 0), 0));
const loggedDays = computed(() => new Set(props.meals.map((m) => String(m.meal_date).slice(0, 10))).size);
const dailyAverage = computed(() => {
    const total = props.meals.reduce((sum, m) => sum + Number(m.calories || 0), 0);
    return loggedDays.value ? Math.round(total / loggedDays.value) : 0;
});

const deleting = ref(null);
const deleteBusy = ref(false);

function confirmDelete() {
    deleteBusy.value = true;
    router.delete(`/meals/${deleting.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deleteBusy.value = false;
            deleting.value = null;
        },
    });
}
</script>

<template>
    <AppLayout>
        <Head title="Meals" />

        <PageHeader title="Meals" subtitle="Your food diary. Log every meal to grow your streak and earn points.">
            <template #actions>
                <Link href="/meals/create" class="btn-primary"><Icon name="plus" class="h-4 w-4" /> Add meal</Link>
            </template>
        </PageHeader>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 lg:gap-6">
            <StatCard label="Today" :value="formatNumber(todayCalories)" unit="kcal" icon="utensils" tone="brand" :hint="`${todayMeals.length} meal${todayMeals.length === 1 ? '' : 's'} logged`" />
            <StatCard label="Daily average" :value="formatNumber(dailyAverage)" unit="kcal" icon="activity" tone="sky" hint="Across days with meals" />
            <StatCard label="Meals logged" :value="formatNumber(meals.length)" icon="calendar" tone="amber" :hint="`Over ${loggedDays} day${loggedDays === 1 ? '' : 's'}`" />
            <StatCard label="Streak" :value="user.meal_streak ?? 0" :unit="user.meal_streak === 1 ? 'day' : 'days'" icon="flame" tone="orange" :hint="`${formatNumber(user.meal_points)} points earned`" />
        </div>

        <div v-if="meals.length" class="mt-6 flex items-center justify-between gap-3">
            <h2 class="card-title">Diary</h2>
            <div class="relative w-full max-w-xs">
                <Icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                <input v-model="search" type="search" class="input pl-9" placeholder="Search meals" aria-label="Search meals" />
            </div>
        </div>

        <div v-if="meals.length === 0" class="card mt-6">
            <EmptyState icon="utensils" title="No meals logged yet" description="Start your food diary — your first meal starts your streak.">
                <Link href="/meals/create" class="btn-primary"><Icon name="plus" class="h-4 w-4" /> Log your first meal</Link>
            </EmptyState>
        </div>

        <div v-else-if="groups.length === 0" class="card mt-4">
            <EmptyState icon="search" title="No matches" :description="`No meals match “${search}”.`" />
        </div>

        <div v-else class="mt-4 space-y-6">
            <section v-for="group in groups" :key="group.date">
                <div class="mb-2 flex items-baseline justify-between px-1">
                    <h3 class="text-sm font-semibold">{{ group.date === 'undated' ? 'No date' : relativeDay(group.date) }}</h3>
                    <p class="text-sm text-muted"><span class="font-semibold tabular-nums text-ink">{{ formatNumber(group.total) }}</span> kcal</p>
                </div>
                <ul class="card divide-y divide-line overflow-hidden">
                    <li v-for="meal in group.meals" :key="meal.id" class="group flex items-center gap-4 p-4 transition hover:bg-subtle/50 sm:px-5">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-subtle text-xl">
                            {{ mealType(meal.meal_time)?.emoji ?? '🍽️' }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold">{{ meal.name }}</p>
                                <span v-if="mealType(meal.meal_time)" class="badge-neutral">{{ mealType(meal.meal_time).label }}</span>
                            </div>
                            <p v-if="meal.description" class="mt-0.5 line-clamp-1 text-sm text-muted">{{ meal.description }}</p>
                            <p v-if="meal.meal_time" class="mt-0.5 inline-flex items-center gap-1 text-xs text-muted sm:hidden">
                                <Icon name="clock" class="h-3 w-3" /> {{ formatTime(meal.meal_time) }}
                            </p>
                        </div>
                        <div class="hidden text-right sm:block">
                            <p class="font-semibold tabular-nums">{{ formatNumber(meal.calories) }} <span class="text-xs font-medium text-muted">kcal</span></p>
                            <p v-if="meal.meal_time" class="text-xs text-muted">{{ formatTime(meal.meal_time) }}</p>
                        </div>
                        <p class="font-semibold tabular-nums sm:hidden">{{ formatNumber(meal.calories) }}</p>
                        <div class="flex shrink-0 gap-1">
                            <Link :href="`/meals/${meal.id}/edit`" class="btn-icon h-8 w-8" aria-label="Edit meal" title="Edit">
                                <Icon name="pencil" class="h-4 w-4" />
                            </Link>
                            <button class="btn-icon h-8 w-8 hover:text-rose-600" aria-label="Delete meal" title="Delete" @click="deleting = meal">
                                <Icon name="trash" class="h-4 w-4" />
                            </button>
                        </div>
                    </li>
                </ul>
            </section>
        </div>

        <ConfirmDialog
            :show="!!deleting"
            title="Delete meal?"
            :message="deleting ? `“${deleting.name}” will be removed and 10 points deducted.` : ''"
            :processing="deleteBusy"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </AppLayout>
</template>

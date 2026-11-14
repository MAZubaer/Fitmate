<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icon.vue';

defineProps({
    form: { type: Object, required: true }, // Inertia useForm instance
    submitLabel: { type: String, default: 'Save meal' },
});

defineEmits(['submit']);

const presets = [
    { label: 'Breakfast', time: '08:00', emoji: '🥣' },
    { label: 'Lunch', time: '13:00', emoji: '🥗' },
    { label: 'Snack', time: '16:30', emoji: '🍎' },
    { label: 'Dinner', time: '20:00', emoji: '🍲' },
];
</script>

<template>
    <form class="card card-pad space-y-5" @submit.prevent="$emit('submit')">
        <div>
            <label class="label" for="meal-name">Meal name</label>
            <input id="meal-name" v-model="form.name" class="input" placeholder="e.g. Chicken rice bowl" autocomplete="off" required />
            <p v-if="form.errors.name" class="field-error">{{ form.errors.name }}</p>
        </div>

        <div>
            <label class="label" for="meal-desc">Notes <span class="font-normal text-muted">(optional)</span></label>
            <textarea id="meal-desc" v-model="form.description" rows="3" class="input resize-none" placeholder="Ingredients, portion size, how you felt…" />
            <p v-if="form.errors.description" class="field-error">{{ form.errors.description }}</p>
        </div>

        <div>
            <label class="label" for="meal-cal">Calories</label>
            <div class="relative">
                <input id="meal-cal" v-model="form.calories" type="number" min="0" inputmode="numeric" class="input pr-14" placeholder="0" />
                <span class="pointer-events-none absolute inset-y-0 right-3.5 flex items-center text-sm text-muted">kcal</span>
            </div>
            <p v-if="form.errors.calories" class="field-error">{{ form.errors.calories }}</p>
        </div>

        <div>
            <span class="label">When</span>
            <div class="mb-3 grid grid-cols-2 gap-2 sm:grid-cols-4">
                <button
                    v-for="p in presets"
                    :key="p.label"
                    type="button"
                    class="flex items-center justify-center gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium transition"
                    :class="String(form.meal_time).slice(0, 5) === p.time ? 'border-brand-500 bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'border-line text-muted hover:text-ink'"
                    @click="form.meal_time = p.time"
                >
                    <span>{{ p.emoji }}</span> {{ p.label }}
                </button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="sr-only" for="meal-date">Date</label>
                    <input id="meal-date" v-model="form.meal_date" type="date" class="input" />
                    <p v-if="form.errors.meal_date" class="field-error">{{ form.errors.meal_date }}</p>
                </div>
                <div>
                    <label class="sr-only" for="meal-time">Time</label>
                    <input id="meal-time" v-model="form.meal_time" type="time" class="input" />
                    <p v-if="form.errors.meal_time" class="field-error">{{ form.errors.meal_time }}</p>
                </div>
            </div>
            <p class="mt-2 flex items-center gap-1.5 text-xs text-muted">
                <Icon name="bell" class="h-3.5 w-3.5" /> Setting a time also schedules a reminder.
            </p>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-line pt-5 sm:flex-row sm:justify-end">
            <Link href="/meals" class="btn-ghost">Cancel</Link>
            <button type="submit" class="btn-primary" :disabled="form.processing">
                {{ form.processing ? 'Saving…' : submitLabel }}
            </button>
        </div>
    </form>
</template>

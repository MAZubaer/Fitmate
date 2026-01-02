<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'

defineProps({
    meals: Array
})
</script>

<template>
<AppLayout>

<div class="max-w-7xl mx-auto px-6 py-10">

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            🍽 My Meals
        </h1>

        <Link
            href="/meals/create"
            class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:opacity-90 text-white px-6 py-3 rounded-xl shadow font-semibold"
        >
            + Add Meal
        </Link>
    </div>

    <div v-if="meals.length === 0" class="text-center text-gray-500 mt-20">
        No meals logged yet 🍽
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="meal in meals" :key="meal.id"
             class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 border border-gray-100 dark:border-gray-700">

            <div class="flex justify-between items-start">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                    {{ meal.name }}
                </h2>
                <span class="text-sm text-gray-500">
                    {{ meal.meal_date }}
                </span>
            </div>

            <p class="text-gray-500 mt-2">{{ meal.description }}</p>

            <div class="mt-4 flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500">Calories</p>
                    <p class="text-2xl font-bold text-green-600">
                        {{ meal.calories }} kcal
                    </p>
                </div>

                <p class="text-sm text-gray-500">
                    ⏰ {{ meal.meal_time }}
                </p>
            </div>

            <div class="mt-6 flex justify-between">
                <Link
                    :href="`/meals/${meal.id}/edit`"
                    class="text-blue-600 font-semibold hover:underline"
                >
                    Edit
                </Link>

                <Link
                    method="delete"
                    :href="`/meals/${meal.id}`"
                    as="button"
                    class="text-red-600 font-semibold hover:underline"
                >
                    Delete
                </Link>
            </div>
        </div>
    </div>

</div>
</AppLayout>
</template>

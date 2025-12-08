<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    meals: Array
});

function deleteMeal(id) {
    if (confirm("Are you sure you want to delete this meal?")) {
        router.delete(`/meals/${id}`);
    }
}
</script>

<template>
    <AppLayout title="Meals">
        <div class="max-w-4xl mx-auto mt-10">

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">My Meals</h1>

                <Link href="/meals/create">
                    <PrimaryButton>+ Add Meal</PrimaryButton>
                </Link>
            </div>

            <div v-if="meals.length === 0" class="text-gray-600">
                No meals added yet.
            </div>

            <div v-else class="space-y-4">
                <div v-for="meal in meals" :key="meal.id" class="p-4 bg-white shadow rounded">
                    <h2 class="font-semibold text-lg">{{ meal.name }}</h2>
                    <p class="text-gray-600">{{ meal.description }}</p>
                    <p class="text-gray-700 mt-2"><b>Calories:</b> {{ meal.calories ?? 'N/A' }}</p>
                    <p class="text-gray-700"><b>Date:</b> {{ meal.meal_date ?? 'N/A' }}</p>

                    <div class="mt-4 flex gap-4">
                        <Link
                            :href="`/meals/${meal.id}/edit`"
                            class="text-blue-600 font-semibold"
                        >
                            Edit
                        </Link>

                        <button
                            @click="deleteMeal(meal.id)"
                            class="text-red-600 font-semibold"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

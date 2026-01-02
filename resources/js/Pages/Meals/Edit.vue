<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    meal: Object
})

const form = useForm({
    name: props.meal.name,
    description: props.meal.description,
    calories: props.meal.calories,
    meal_date: props.meal.meal_date,
    meal_time: props.meal.meal_time
})

const submit = () => {
    form.put(`/meals/${props.meal.id}`)
}
</script>

<template>
<AppLayout>

<div class="max-w-3xl mx-auto py-10 px-6">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl p-8">

        <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">
            ✏ Edit Meal
        </h1>

        <form @submit.prevent="submit" class="space-y-5">

            <input v-model="form.name"
                class="w-full p-3 rounded-lg border dark:bg-gray-800 dark:border-gray-700"/>

            <textarea v-model="form.description"
                class="w-full p-3 rounded-lg border dark:bg-gray-800 dark:border-gray-700"/>

            <input v-model="form.calories" type="number"
                class="w-full p-3 rounded-lg border dark:bg-gray-800 dark:border-gray-700"/>

            <div class="grid grid-cols-2 gap-4">
                <input v-model="form.meal_date" type="date"
                    class="p-3 rounded-lg border dark:bg-gray-800 dark:border-gray-700"/>
                <input v-model="form.meal_time" type="time"
                    class="p-3 rounded-lg border dark:bg-gray-800 dark:border-gray-700"/>
            </div>

            <button
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 rounded-xl font-bold shadow hover:opacity-90">
                Update Meal
            </button>

        </form>
    </div>
</div>

</AppLayout>
</template>

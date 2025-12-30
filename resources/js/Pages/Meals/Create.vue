<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    name: '',
    description: '',
    calories: '',
    meal_date: '',
    meal_time: '',
});

function submit() {
    form.post('/meals');
}
</script>

<template>
    <AppLayout title="Add Meal">

        <div class="max-w-2xl mx-auto mt-10">
            <h1 class="text-2xl font-bold mb-6">Add New Meal</h1>

            <form @submit.prevent="submit" class="space-y-4">

                <div>
                    <label class="font-semibold">Meal Name</label>
                    <input v-model="form.name" class="input" required>
                </div>

                <div>
                    <label class="font-semibold">Description</label>
                    <textarea v-model="form.description" class="input"></textarea>
                </div>

                <div>
                    <label class="font-semibold">Calories</label>
                    <input type="number" v-model="form.calories" class="input">
                </div>


                <div>
                    <label class="font-semibold">Meal Date</label>
                    <input type="date" v-model="form.meal_date" class="input">
                </div>

                <div>
                    <label class="font-semibold">Meal Time</label>
                    <input type="time" v-model="form.meal_time" class="input">
                </div>

                <PrimaryButton :disabled="form.processing">
                    Save Meal
                </PrimaryButton>

            </form>
        </div>

    </AppLayout>
</template>

<style>
.input {
    @apply w-full border-gray-300 rounded mt-1 p-2;
}
</style>

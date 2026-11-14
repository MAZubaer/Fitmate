<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import MealForm from '@/Components/MealForm.vue';
import PageHeader from '@/Components/PageHeader.vue';

const props = defineProps({
    meal: Object,
});

const form = useForm({
    name: props.meal.name,
    description: props.meal.description ?? '',
    calories: props.meal.calories ?? '',
    meal_date: props.meal.meal_date ? String(props.meal.meal_date).slice(0, 10) : '',
    meal_time: props.meal.meal_time ? String(props.meal.meal_time).slice(0, 5) : '',
});

const submit = () => form.put(`/meals/${props.meal.id}`);
</script>

<template>
    <AppLayout>
        <Head title="Edit meal" />
        <div class="mx-auto max-w-2xl">
            <Link href="/meals" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-muted hover:text-ink">
                <Icon name="chevron-left" class="h-4 w-4" /> Meals
            </Link>
            <PageHeader title="Edit meal" :subtitle="meal.name" />
            <MealForm :form="form" submit-label="Save changes" @submit="submit" />
        </div>
    </AppLayout>
</template>

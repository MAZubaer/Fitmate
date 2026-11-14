<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import MealForm from '@/Components/MealForm.vue';
import PageHeader from '@/Components/PageHeader.vue';
import { todayKey } from '@/utils/format';

const form = useForm({
    name: '',
    description: '',
    calories: '',
    meal_date: todayKey(),
    meal_time: '',
});

const submit = () => form.post('/meals');
</script>

<template>
    <AppLayout>
        <Head title="Add meal" />
        <div class="mx-auto max-w-2xl">
            <Link href="/meals" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-muted hover:text-ink">
                <Icon name="chevron-left" class="h-4 w-4" /> Meals
            </Link>
            <PageHeader title="Add a meal" subtitle="Each meal you log earns 10 points and keeps your streak going." />
            <MealForm :form="form" submit-label="Save meal" @submit="submit" />
        </div>
    </AppLayout>
</template>

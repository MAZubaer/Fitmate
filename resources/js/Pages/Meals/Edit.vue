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
  <div class="flex min-h-screen items-center justify-center bg-gray-100 px-3 py-6 sm:px-6 sm:py-10">
    <div class="w-full max-w-3xl rounded-3xl bg-gradient-to-br from-[#0F2027] via-[#203A43] to-[#2C5364] p-5 text-white shadow-2xl sm:p-10">

      <h1 class="text-3xl font-bold mb-8 flex items-center gap-3">
        ✏️ Edit Meal
      </h1>

      <form @submit.prevent="submit" class="space-y-6">

        <input
          v-model="form.name"
          class="w-full px-5 py-4 rounded-xl bg-black/30 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>

        <textarea
          v-model="form.description"
          rows="3"
          class="w-full px-5 py-4 rounded-xl bg-black/30 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>

        <input
          v-model="form.calories"
          type="number"
          class="w-full px-5 py-4 rounded-xl bg-black/30 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
          <input
            v-model="form.meal_date"
            type="date"
            class="px-5 py-4 rounded-xl bg-black/30 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>

          <input
            v-model="form.meal_time"
            type="time"
            class="px-5 py-4 rounded-xl bg-black/30 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"/>
        </div>

        <button
          class="w-full mt-6 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold text-lg shadow-lg hover:opacity-90 transition">
          Update Meal
        </button>

      </form>
    </div>
  </div>
</AppLayout>
</template>

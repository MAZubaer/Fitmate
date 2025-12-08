<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';


const message = ref('');


const workouts = ref([]);
const form = ref({ name: '', sets: '', reps: '', date: '', time: '' });
const editingId = ref(null);
const editForm = ref({});
const deletingId = ref(null);


const showMessage = (text) => {
    message.value = text;
    setTimeout(() => (message.value = ''), 3000);
};


const addWorkout = () => {
    if (!form.value.name) return;
    workouts.value.push({ id: Date.now(), ...form.value });
    form.value = { name: '', sets: '', reps: '', date: '', time: '' };
    showMessage('Workout added successfully!');
};


const editWorkout = (workout) => {
    editingId.value = workout.id;
    editForm.value = { ...workout };
};


const updateWorkout = (id) => {
    const index = workouts.value.findIndex(w => w.id === id);
    if (index !== -1) {
        workouts.value[index] = { ...editForm.value, id };
        editingId.value = null;
        showMessage('Workout updated successfully!');
    }
};


const confirmDelete = (id) => {
    deletingId.value = id;
};

const cancelDelete = () => {
    deletingId.value = null;
};


const deleteWorkout = (id) => {
    workouts.value = workouts.value.filter(w => w.id !== id);
    deletingId.value = null;
    showMessage('Workout deleted successfully!');
};
</script>

<template>
<AppLayout>
  <Head title="Workout Tracker" />

  <div class="min-h-screen bg-gray-100 py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 font-sans">

    
    <div class="bg-sky-400 rounded-lg shadow-2xl p-8 mb-6 text-center">
      <h1 class="text-4xl font-extrabold italic mb-2 tracking-wide text-navy-900">Workout Tracker</h1>
      <p class="text-lg font-semibold italic text-navy-800">Your personal fitness checklist</p>
    </div>

    
    <div v-if="message" class="mb-4 p-3 rounded shadow bg-sky-100 text-navy-900 font-semibold text-center">
      {{ message }}
    </div>

    
    <div class="bg-gray-200 rounded-xl shadow-lg p-6 mb-8 border border-gray-300">
      <h2 class="text-2xl font-bold mb-4 text-navy-900">Add New Workout</h2>
      <form @submit.prevent="addWorkout" class="grid gap-4">
        <input v-model="form.name" placeholder="Workout Name" class="input font-semibold italic" required>
        <div class="grid grid-cols-2 gap-2">
          <input v-model="form.sets" type="number" placeholder="Sets" class="input font-semibold" required>
          <input v-model="form.reps" type="number" placeholder="Reps" class="input font-semibold" required>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <input v-model="form.date" type="date" class="input font-semibold" required>
          <input v-model="form.time" type="time" class="input font-semibold" required>
        </div>
        <button type="submit" class="btn-primary w-full shadow-lg hover:scale-105 transition-transform">
          Add Workout
        </button>
      </form>
    </div>

    <!-- WORKOUT LIST -->
    <div v-if="workouts.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="workout in workouts" :key="workout.id" class="bg-gray-50 shadow-lg rounded-xl p-5 relative border border-gray-300 hover:shadow-2xl transition-shadow">

        <!-- TOP-RIGHT BUTTONS -->
        <div class="absolute top-4 right-4 flex space-x-2">
          <button @click="editWorkout(workout)" class="btn-edit shadow hover:scale-105 transition-transform">Update</button>
          <button v-if="deletingId !== workout.id" @click="confirmDelete(workout.id)" class="btn-delete shadow hover:scale-105 transition-transform">Delete</button>
        </div>

        <!-- WORKOUT INFO -->
        <h3 class="text-xl font-bold italic text-navy-900 mb-2 mt-4">{{ workout.name }}</h3>
        <p class="text-navy-800 font-medium mb-1">{{ workout.sets }} sets count· {{ workout.reps }} reps</p>
        <p class="text-navy-700 italic">{{ workout.date }} at {{ workout.time }}</p>

        <!-- DELETE CONFIRMATION -->
        <div v-if="deletingId === workout.id" class="mt-3 flex space-x-2 items-center">
          <span class="text-red-600 font-bold italic">Are you sure?</span>
          <button @click="deleteWorkout(workout.id)" class="btn-delete shadow hover:scale-105 transition-transform">Yes</button>
          <button @click="cancelDelete" class="btn-edit shadow hover:scale-105 transition-transform">Cancel</button>
        </div>

        
        <div v-if="editingId === workout.id" class="mt-4 bg-gray-100 p-4 rounded-lg border border-gray-300">
          <input v-model="editForm.name" class="input mb-2 font-semibold italic" placeholder="Workout Name">
          <div class="grid grid-cols-2 gap-2 mb-2">
            <input v-model="editForm.sets" type="number" class="input font-semibold" placeholder="Sets">
            <input v-model="editForm.reps" type="number" class="input font-semibold" placeholder="Reps">
          </div>
          <div class="grid grid-cols-2 gap-2 mb-2">
            <input v-model="editForm.date" type="date" class="input font-semibold">
            <input v-model="editForm.time" type="time" class="input font-semibold">
          </div>
          <button @click="updateWorkout(workout.id)" class="btn-primary w-full shadow-lg hover:scale-105 transition-transform">Save Changes</button>
        </div>

      </div>
    </div>

    <p v-else class="text-navy-700 mt-6 italic font-medium text-center">No workouts added yet.</p>

  </div>
</AppLayout>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;700&display=swap');

.font-sans { font-family: 'Roboto Slab', serif; }

.input {
  @apply border border-gray-400 rounded-lg px-3 py-2 w-full focus:ring focus:ring-sky-200 text-navy-900;
}
.btn-primary {
  @apply bg-gradient-to-r from-sky-500 to-sky-400 text-white font-bold px-4 py-2 rounded-lg hover:from-sky-600 hover:to-sky-500 transition;
}
.btn-edit {
  @apply bg-gray-500 text-white px-3 py-1 rounded-lg font-semibold hover:bg-gray-600 transition;
}
.btn-delete {
  @apply bg-red-600 text-white px-3 py-1 rounded-lg font-semibold hover:bg-red-700 transition;
}
/* Text color classes for convenience */
.text-navy-900 { color: #001F3F; }
.text-navy-800 { color: #003366; }
.text-navy-700 { color: #004080; }
</style>


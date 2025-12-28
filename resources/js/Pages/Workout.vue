<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axios from 'axios'


const message = ref('')
const workouts = ref([])
const currentPlan = ref([])

const form = ref({
    name: '',
    sets: '',
    reps: '',
    date: '',
    time: ''
})

const editingId = ref(null)
const editForm = ref({})
const deletingId = ref(null)

/* ---------------- HELPERS ---------------- */
const flash = (text) => {
    message.value = text
    setTimeout(() => message.value = '', 3000)
}


const loadWorkouts = async () => {
    try {
        const res = await axios.get('/workouts-data')
        workouts.value = res.data
    } catch (e) {
        flash('Failed to load workouts')
    }
}

onMounted(loadWorkouts)

/* ---------------- ADD WORKOUT ---------------- */

const addWorkout = async () => {
  // Validate on frontend
  if (!form.value.name) return flash('Exercise name is required')
  if (!form.value.sets || isNaN(form.value.sets) || form.value.sets < 1) return flash('Sets must be a positive number')
  if (!form.value.reps || isNaN(form.value.reps) || form.value.reps < 1) return flash('Reps must be a positive number')
  if (!form.value.date) return flash('Date is required')
  if (!form.value.time) return flash('Time is required')

  // Ensure sets/reps are integers
  const payload = {
    ...form.value,
    sets: parseInt(form.value.sets),
    reps: parseInt(form.value.reps)
  }

  try {
    const res = await axios.post('/workouts-data', payload)
    workouts.value.unshift(res.data)
    flash('Workout saved to database')
    resetForm()
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
      // Show first validation error
      const errors = e.response.data.errors
      const firstError = Object.values(errors)[0][0]
      flash(firstError)
    } else {
      flash('Failed to save workout')
    }
    console.error(e)
  }
}

/* ---------------- ADD TO PLAN ---------------- */
const addToPlan = () => {
    if (!form.value.name) return flash('Exercise name is required')
    currentPlan.value.push({ id: Date.now(), ...form.value })
    flash('Exercise added to current plan')
    resetForm()
}

/* ---------------- SAVE PLAN ---------------- */
const savePlan = async () => {
  if (currentPlan.value.length === 0) {
    flash('No exercises in plan')
    return
  }

  try {
    for (const w of currentPlan.value) {
      // Validate and convert sets/reps
      if (!w.name || !w.sets || isNaN(w.sets) || w.sets < 1 || !w.reps || isNaN(w.reps) || w.reps < 1 || !w.date || !w.time) {
        flash('Invalid exercise in plan, skipping')
        continue
      }
      const payload = {
        ...w,
        sets: parseInt(w.sets),
        reps: parseInt(w.reps)
      }
      const res = await axios.post('/workouts-data', payload)
      workouts.value.unshift(res.data)
    }
    currentPlan.value = []
    flash('Workout plan saved to database')
  } catch (e) {
    if (e.response && e.response.data && e.response.data.errors) {
      const errors = e.response.data.errors
      const firstError = Object.values(errors)[0][0]
      flash(firstError)
    } else {
      flash('Failed to save plan')
    }
    console.error(e)
  }
}


const clearPlan = () => {
    currentPlan.value = []
    flash('Workout plan cleared')
}


const editWorkout = (workout) => {
    editingId.value = workout.id
    editForm.value = { ...workout }
}

const updateWorkout = async (id) => {
    try {
        const res = await axios.put(`/workouts-data/${id}`, editForm.value)
        const index = workouts.value.findIndex(w => w.id === id)
        workouts.value[index] = res.data
        editingId.value = null
        flash('Workout updated in database')
    } catch (e) {
        console.error(e)
        flash('Failed to update workout')
    }
}

const cancelEdit = () => {
    editingId.value = null
    editForm.value = {}
}


const confirmDelete = (id) => deletingId.value = id
const cancelDelete = () => deletingId.value = null

const deleteWorkout = async (id) => {
    try {
        await axios.delete(`/workouts-data/${id}`)
        workouts.value = workouts.value.filter(w => w.id !== id)
        deletingId.value = null
        flash('Workout deleted from database')
    } catch (e) {
        console.error(e)
        flash('Failed to delete workout')
    }
}


const resetForm = () => {
    form.value = { name: '', sets: '', reps: '', date: '', time: '' }
}
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

  <div class="max-w-6xl mx-auto p-6">

    
    <div class="bg-white p-4 rounded shadow mb-6">
      <h2 class="text-xl font-bold mb-3">Add Exercise</h2>
      <input v-model="form.name" placeholder="Exercise name" class="input mb-2" />
      <div class="grid grid-cols-2 gap-2">
        <input v-model="form.sets" type="number" placeholder="Sets" class="input" />
        <input v-model="form.reps" type="number" placeholder="Reps" class="input" />
      </div>
      <div class="grid grid-cols-2 gap-2 mt-2">
        <input v-model="form.date" type="date" class="input" />
        <input v-model="form.time" type="time" class="input" />
      </div>

      <div class="flex gap-3 mt-4">
        <button @click="addWorkout" class="btn"> Add Workout</button>
        <button @click="addToPlan" class="btn-sm">⭐ Add to Current Workout Plan</button>
        <button @click="clearPlan" class="btn-sm text-red-600"> Clear Plan</button>
      </div>
    </div>

    <!-- CURRENT PLAN -->
    <div class="bg-gray-50 p-4 rounded shadow mb-6">
      <h2 class="text-xl font-bold mb-3">Current Workout Plan</h2>
      <p v-if="!currentPlan.length" class="italic text-gray-600">No exercises added yet</p>
      <ul v-else class="list-disc pl-5 mb-3">
        <li v-for="p in currentPlan" :key="p.id">
          {{ p.name }} — {{ p.sets }} sets × {{ p.reps }} reps
        </li>
      </ul>
      <button @click="savePlan" class="btn"> Save Workout Plan</button>
    </div>

    <!-- SAVED WORKOUTS -->
    <div>
      <h2 class="text-2xl font-bold mb-4">List Of Future Workouts Plan</h2>
      <p v-if="!workouts.length" class="italic text-gray-600">No workouts saved yet</p>

      <div v-for="w in workouts" :key="w.id" class="bg-gray-100 p-4 rounded mb-3 relative">
        <strong>{{ w.name }}</strong><br>
        {{ w.sets }} sets × {{ w.reps }} reps<br>
        {{ w.date }} at {{ w.time }}

        <div class="absolute top-2 right-2 flex space-x-2">
          <button @click="editWorkout(w)" class="btn-sm">Edit</button>
          <button v-if="deletingId !== w.id" @click="confirmDelete(w.id)" class="btn-sm text-red-600">Delete</button>
        </div>

        <div v-if="editingId === w.id" class="mt-3">
          <input v-model="editForm.name" class="input mb-1" placeholder="Name" />
          <input v-model="editForm.sets" type="number" class="input mb-1" placeholder="Sets" />
          <input v-model="editForm.reps" type="number" class="input mb-1" placeholder="Reps" />
          <input v-model="editForm.date" type="date" class="input mb-1" />
          <input v-model="editForm.time" type="time" class="input mb-1" />
          <div class="flex gap-2 mt-2">
            <button @click="updateWorkout(w.id)" class="btn">Save Changes</button>
            <button @click="cancelEdit" class="btn-sm bg-gray-300 text-black">Cancel</button>
          </div>
        </div>

        <div v-if="deletingId === w.id" class="mt-2 flex space-x-2 items-center">
          <span class="text-red-600 font-bold italic">Confirm delete?</span>
          <button @click="deleteWorkout(w.id)" class="btn-sm text-red-600">Yes</button>
          <button @click="cancelDelete" class="btn-sm">Cancel</button>
        </div>
      </div>
    </div>

  </div>
</div>
</AppLayout>
</template>

<style>
.input { border: 1px solid #ccc; padding: 8px; border-radius: 6px; width: 100%; }
.btn { background: #0ea5e9; color: white; padding: 8px 16px; border-radius: 6px; }
.btn-sm { padding: 4px 8px; border: 1px solid #ccc; border-radius: 6px; cursor: pointer; }
.btn-sm:hover { background: #e2e8f0; }
</style>

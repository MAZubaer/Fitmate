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
  time: '',
  calories: '',
  duration: '' 
})

const editingId = ref(null)
const editForm = ref({})

editForm.value.duration = ''    

const deletingId = ref(null)
const showModal = ref(false)

const searchQuery = ref('')          
const filteredWorkouts = ref([])     

const flash = (text) => {
  message.value = text
  setTimeout(() => { message.value = '' }, 2500)
}

const loadWorkouts = async () => {
  try {
    const res = await axios.get('/workouts-data')
    workouts.value = res.data.map(w => ({ ...w, completed: w.completed || false }))
    filteredWorkouts.value = workouts.value
  } catch {
    flash('Failed to load workouts')
  }
}

const performSearch = () => {
  if (!searchQuery.value) {
    filteredWorkouts.value = workouts.value
    return
  }
  filteredWorkouts.value = workouts.value.filter(w =>
    w.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
}

const addWorkout = async () => {
  if (!form.value.name) return flash('Exercise name is required')
  try {
    const res = await axios.post('/workouts-data', form.value)
    workouts.value.unshift({ ...res.data, completed: false })
    filteredWorkouts.value = workouts.value
    flash('Workout added successfully!')
    resetForm()
  } catch {
    flash('Failed to save workout')
  }
}

const addToPlan = () => {
  if (!form.value.name) return flash('Exercise name is required')
  currentPlan.value.push({ id: Date.now(), ...form.value })
  flash('Added to current workout plan!')
  resetForm()
}

const savePlan = async () => {
  if (!currentPlan.value.length) return flash('No exercises in plan')
  try {
    for (const w of currentPlan.value) {
      const res = await axios.post('/workouts-data', w)
      workouts.value.unshift({ ...res.data, completed: false })
    }
    currentPlan.value = []
    filteredWorkouts.value = workouts.value
    flash('Plan saved successfully!')
  } catch {
    flash('Failed to save plan')
  }
}

const clearPlan = () => {
  currentPlan.value = []
  flash('Plan cleared!')
}

const editWorkout = (workout) => {
  editingId.value = workout.id
  editForm.value = { ...workout }
}

const updateWorkout = async (id) => {
  try {
    const res = await axios.put(`/workouts-data/${id}`, editForm.value)
    const i = workouts.value.findIndex(w => w.id === id)
    workouts.value[i] = { ...res.data, completed: workouts.value[i].completed }
    filteredWorkouts.value = workouts.value
    editingId.value = null
    flash('Workout updated!')
  } catch {
    flash('Failed to update workout')
  }
}

const confirmDelete = (id) => deletingId.value = id
const cancelDelete = () => deletingId.value = null

const deleteWorkout = async (id) => {
  try {
    await axios.delete(`/workouts-data/${id}`)
    workouts.value = workouts.value.filter(w => w.id !== id)
    filteredWorkouts.value = workouts.value
    deletingId.value = null
    flash('Workout deleted!')
  } catch {
    flash('Delete failed')
  }
}

const completeWorkout = async (workout) => {
  try {
    const updated = { ...workout, completed: true }
    const res = await axios.put(`/workouts-data/${workout.id}`, updated)
    const i = workouts.value.findIndex(w => w.id === workout.id)
    workouts.value[i] = res.data
    filteredWorkouts.value = workouts.value
    flash(`Workout "${workout.name}" marked as completed ✅`)
  } catch {
    flash('Failed to save completion status')
  }
}

const cancelEdit = () => editingId.value = null

const totalHistoryCalories = ref(0)

const calcTotalCalories = () => {
  totalHistoryCalories.value = workouts.value
    .filter(w => w.completed)
    .reduce((sum, w) => sum + Number(w.calories || 0), 0)
}

const resetForm = () => {
  form.value = { name: '', sets: '', reps: '', date: '', time: '', calories: '', duration: '' }
}

onMounted(loadWorkouts)
</script>

<template>
<AppLayout>
<Head title="Workout Tracker" />

<div v-if="message"
     class="fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded shadow-lg z-50 animate-slide-in">
  {{ message }}
</div>

<div class="min-h-screen py-4 bg-gradient-to-b from-sky-200 via-sky-50 to-blue-100">
  <div class="text-center mb-6">
    <h1 class="text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-blue-500 via-blue-500 to-pink-500 drop-shadow-lg">
      Workout Tracker
    </h1>
  </div>

  <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-x-2 gap-y-0 px-2">

    <!-- LEFT -->
    <div class="bg-purple-200 rounded-2xl shadow flex flex-col">
      <div class="p-2 border-b"></div>

      <div class="p-1 overflow-y-auto flex-1">
        <div class="mb-3">
          <button 
          @click="showModal = true" 
            class="w-full px-6 py-3 font-semibold text-white rounded-xl shadow-lg bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 hover:from-blue-700 hover:via-blue-600 hover:to-indigo-700 transform hover:-translate-y-1 transition-all duration-300">
            Add New Workout
            </button>
        </div>




        <hr class="mb-5 border-gray-300">
         <h3 class="text-lg font-bold mb-2 text-blue-900">To-do List of My Workouts</h3>


        <!-- SEARCH INPUT -->
<div class="mb-3 flex gap-2 items-center">
  <input
    v-model="searchQuery"
    type="text"
    placeholder="Search workouts..."
    class="input flex-1"
  >
  <button @click="performSearch" class="btn-sm bg-gray-200">Search</button>
  <!-- Back button -->
  <button @click="() => { searchQuery=''; filteredWorkouts=workouts }" 
          class="btn-sm bg-gray-200 text-black">
    Back
  </button>
</div>


        <div
         v-for="w in filteredWorkouts"
         :key="w.id"
          :class="[
             'bg-white p-4 rounded-xl shadow-sm hover:shadow-md transition-all card-anim border mb-3',
            w.completed ? 'bg-green-50 border-green-200' : 'border-gray-200'
          ]">

          <div class="flex justify-between items-start">
            <div>
                 <strong>{{ w.name }}</strong>

               <!-- Completed Badge -->
                <span
                v-if="w.completed"
                 class="completed-badge ml-2"
>
              ✅ Completed
               </span>

              <div class="mt-2 flex gap-2 flex-wrap">
                <span class="badge badge-blue">Sets: {{ w.sets }}</span>
                 <span class="badge badge-purple">Reps: {{ w.reps }}</span>
               <span class="badge badge-green">{{ w.calories }} cal</span>
                <span class="badge badge-blue">Duration: {{ w.duration }} sec</span>
               </div>

              <p class="text-sm text-gray-600 mt-1">
           {{ w.date }} {{ w.time }}
          </p>

      </div>

            <div class="flex space-x-2">
              <button @click="editWorkout(w)" class="btn-sm">Edit</button>
              <button v-if="deletingId !== w.id" @click="confirmDelete(w.id)"
                      class="btn-sm text-red-600">Delete</button>
              <button v-if="!w.completed" @click="completeWorkout(w)"
                      class="btn-sm bg-green-500 text-white">Complete</button>
            </div>
          </div>

          <div v-if="editingId === w.id" class="mt-3 space-y-1">
            <input v-model="editForm.name" class="input" placeholder="Name">
            <input v-model="editForm.sets" type="number" class="input" placeholder="Sets">
            <input v-model="editForm.reps" type="number" class="input" placeholder="Reps">
            <input v-model="editForm.calories" type="number" class="input" placeholder="Calories">
            <input v-model="editForm.duration" type="number" class="input" placeholder="Duration (sec)">

            <input v-model="editForm.date" type="date" class="input">
            <input v-model="editForm.time" type="time" class="input">
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

    <!-- RIGHT: HISTORY -->
    <div class="bg-gray-50 p-5 rounded-2xl shadow-sm max-h-[800px] overflow-y-auto">
      <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-red-500 via-blue-500 to-black drop-shadow-lg">
      History (Completed Workouts)
    </h1>
      
      <hr class="mb-4 border-blue-200">

      <p v-if="!workouts.filter(w => w.completed).length"
         class="italic text-gray-600">No completed workouts yet</p>

        <div v-else class="space-y-4">
  <div
    v-for="h in workouts.filter(w => w.completed)"
    :key="h.id"
    class="bg-purple-100 rounded-xl shadow hover:shadow-lg transition-all p-4 flex justify-between items-center group">

    <div>
      <strong class="text-gray-800 text-base">{{ h.name }}</strong>
      <div class="flex gap-2 mt-1 flex-wrap">
        <span class="badge badge-blue">Sets: {{ h.sets }}</span>
        <span class="badge badge-purple">Reps: {{ h.reps }}</span>
        <span class="badge badge-green">{{ h.calories }} cal</span>
        <span class="badge badge-blue">Duration: {{ h.duration }} sec</span>
      </div>
      <p class="text-sm text-gray-500 mt-1">{{ h.date }} {{ h.time }}</p>
    </div>

    <div>
      <button
        v-if="deletingId !== h.id"
        @click="confirmDelete(h.id)"
        class="btn-sm text-red-600 group-hover:scale-105 transition-transform">
        Delete
      </button>

      <div v-else class="flex space-x-2 items-center">
        <span class="text-red-600 font-bold italic">Confirm delete?</span>
        <button @click="deleteWorkout(h.id)" class="btn-sm text-red-600">Yes</button>
        <button @click="cancelDelete" class="btn-sm">No</button>
      </div>
    </div>

  


        </div>
      </div>

      <!-- Summary Cards Grid -->
       <!-- Progress Bar -->
      <div class="mt-4 col-span-full">
        <h4 class="text-sm font-medium text-gray-700 mb-1">Workout Completion</h4>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div 
            class="bg-red-500 h-3 rounded-full transition-all" 
            :style="{ width: `${(workouts.filter(w => w.completed).length / workouts.length) * 100 || 0}%` }">
          </div>
        </div>
        <p class="text-sm text-gray-400 mt-1">
          {{ workouts.filter(w => w.completed).length }} of {{ workouts.length }} completed
        </p>
      </div>

      <div class="mt-6 grid grid-cols-3 gap-3 text-center">
        <div class="bg-blue-100 p-3 rounded-xl shadow flex flex-col items-center">
          <span class="text-lg font-bold">{{ workouts.filter(w => w.completed).length }}</span>
          <span class="text-sm text-gray-500">Completed</span>
        </div>
        <div class="bg-blue-100 p-3 rounded-xl shadow flex flex-col items-center">
          <span class="text-lg font-bold">{{ workouts.reduce((sum, w) => sum + Number(w.sets || 0), 0) }}</span>
          <span class="text-sm text-gray-500">Total Sets</span>
        </div>
        <div class="bg-blue-100 p-3 rounded-xl shadow flex flex-col items-center">
          <span class="text-lg font-bold">{{ workouts.reduce((sum, w) => sum + Number(w.reps || 0), 0) }}</span>
          <span class="text-sm text-gray-500">Total Reps</span>
        </div>

        <div class="bg-blue-100 p-3 rounded-xl shadow flex flex-col items-center">
          <span class="text-lg font-bold">{{ workouts.reduce((sum, w) => sum + Number(w.duration || 0), 0) }}</span>
          <span class="text-sm text-gray-500">Total Duration (sec)</span>
          </div>
        </div>

      <div class="mt-4">
        <button @click="calcTotalCalories" class="btn-sm bg-green-700 text-white hover:bg-green-600">
          Calculate Total Calories Burned
        </button>

        <p v-if="totalHistoryCalories" class="mt-2 font-semibold">
          Total Burned: {{ totalHistoryCalories }} cal
        </p>
      </div>
    </div>

  </div>
</div>

<!-- MODAL -->
<div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-blue-200 p-4 rounded-xl w-110 max-w-md shadow-lg">
    
    <!-- Header -->
    <h3 class="text-xl font-bold mb-3 text-center text-blue-900">
      Adding New Workout Plan
    </h3>

    <div class="space-y-2">
      <input v-model="form.name" class="input" placeholder="Exercise Name">
      <input v-model="form.sets" type="number" class="input" placeholder="Sets">
      <input v-model="form.reps" type="number" class="input" placeholder="Reps">
      <input v-model="form.calories" type="number" class="input" placeholder="Calories">
      <input v-model="form.duration" type="number" class="input" placeholder="Duration (sec)">
      <input v-model="form.date" type="date" class="input">
      <input v-model="form.time" type="time" class="input">
    </div>

    <div class="flex justify-between mt-4">
      <button @click="() => { addWorkout(); showModal=false }" class="btn">
        Save to To-do List
      </button>
      <button @click="showModal=false" class="btn-sm bg-gray-300 text-black">
        Back
      </button>
    </div>
  </div>
</div>


</AppLayout>
</template>

<style>
/* Your original styles remain unchanged */
.card-anim {
  animation: popUp .35s ease forwards;
}

@keyframes popUp {
  from { transform: translateY(8px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
.card-anim:hover {
  box-shadow: 0 8px 22px rgba(0, 0, 0, .04),
              0 4px 12px rgba(0, 0, 0, .06);
}
.badge{
  padding:4px 10px;
  border-radius:999px;
  font-size:12px;
  font-weight:600;
  background:#e5e7eb;
}

.badge-blue{ background:#dbeafe; color:#1d4ed8 }
.badge-purple{ background:#ede9fe; color:#6d28d9 }
.badge-green{ background:#dcfce7; color:#15803d }

.completed-badge{
  color:#15803d;
  font-weight:700;
  animation: pop 0.5s ease forwards;
}

@keyframes pop{
  0%{ transform:scale(0.6); opacity:0 }
  60%{ transform:scale(1.15); opacity:1 }
  100%{ transform:scale(1) }
}
body, button, input {
  font-family: "Poppins", system-ui, -apple-system, Segoe UI, sans-serif;
}

h2, h3 {
  letter-spacing: .3px;
  font-weight: 600;
}

strong {
  font-weight: 600;
}

.text-sm {
  color: #6b7280;
}

.btn, .btn-sm {
  font-weight: 500;
  letter-spacing: .2px;
}

.card-anim {
  font-size: 14.5px;
  line-height: 1.45;
}

.input{border:1px solid #ccc;padding:8px;border-radius:8px;width:100%}
.btn{background:#0ea5e9;color:white;padding:8px 16px;border-radius:8px}
.btn-sm{padding:6px 10px;border:1px solid #ccc;border-radius:8px;cursor:pointer}
.btn-sm.bg-green-500{background:#16a34a;color:white}
.fixed{position:fixed}
.animate-slide-in{animation:slide-in .5s ease forwards}
@keyframes slide-in{0%{transform:translateX(200%);opacity:0}100%{transform:translateX(0);opacity:1}}
</style>

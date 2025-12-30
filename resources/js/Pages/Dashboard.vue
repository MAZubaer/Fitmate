<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import { format, parseISO, subDays } from 'date-fns';
import { toZonedTime, format as tzFormat } from 'date-fns-tz';

Chart.register(...registerables);

const goToExport = () => {
  router.visit('/analytics/export')
}

const props = defineProps({
  user: Object,
  stats: Object,
  calorieChart: Array //
})

// Add methods for quick actions
function goToWorkout() {
  router.visit('/workout');
}
function goToMeals() {
  router.visit('/meals');
}

const showBmiModal = ref(false);
const height = ref('');
const weight = ref('');
const bmi = ref(null);
const bmiError = ref('');
const bmiSuccess = ref('');
const bmiStatus = ref('');
const bmiMessage = ref('');
const showGoalModal = ref(false)
const calorieGoal = ref(props.stats.daily_goal)//
const goalSuccess = ref('')



const bmiMessages = {
  underweight: [
    'Your body may need a little extra fuel. Small, balanced meals and strength training can help you feel stronger every day.',
    'Health is about nourishment and balance. Focus on eating well and caring for your body.',
    'You’re on a journey—building strength and energy is a great next step.'
  ],
  normal: [
    'Great job! Your BMI is within a healthy range—keep maintaining your balanced lifestyle.',
    'Consistency is your superpower. Keep eating well, staying active, and prioritizing rest.',
    'You’re doing well! Small healthy choices every day make a big difference.'
  ],
  overweight: [
    'You’re taking a positive step by checking your health. Small changes can lead to big results.',
    'Progress doesn’t have to be fast—focus on consistency, not perfection.',
    'A balanced diet and regular movement can help you feel more energetic and confident.'
  ],
  obese: [
    'Your health matters, and it’s never too late to start making positive changes.',
    'Every small step counts. Focus on building healthy habits one day at a time.',
    'You’re not alone—support, consistency, and patience can lead to meaningful progress.'
  ]
};

function getBmiStatusAndMessage(bmiValue) {
  let status = '';
  let category = '';
  if (bmiValue < 18.5) {
    status = 'Underweight';
    category = 'underweight';
  } else if (bmiValue < 25) {
    status = 'Normal';
    category = 'normal';
  } else if (bmiValue < 30) {
    status = 'Overweight';
    category = 'overweight';
  } else {
    status = 'Obese';
    category = 'obese';
  }
  const messages = bmiMessages[category];
  const message = messages[Math.floor(Math.random() * messages.length)];
  return { status, message };
}

function calculateBmi() {
  const h = parseFloat(height.value);
  const w = parseFloat(weight.value);
  if (!h || !w || h <= 0 || w <= 0) {
    bmiError.value = 'Please enter valid height and weight.';
    bmi.value = null;
    bmiStatus.value = '';
    bmiMessage.value = '';
    return;
  }
  bmi.value = (w / (h * h)).toFixed(2);
  bmiError.value = '';
  const { status, message } = getBmiStatusAndMessage(Number(bmi.value));
  bmiStatus.value = status;
  bmiMessage.value = message;
}

async function submitBmi() {
  calculateBmi();
  if (bmiError.value) return;
  try {
    await axios.post('/bmi', { height: height.value, weight: weight.value, bmi: bmi.value });
    bmiSuccess.value = 'BMI updated!';
    showBmiModal.value = false;
    await fetchBmiHistory(); // Refresh chart data
  } catch (e) {
    if (e.response && e.response.data && e.response.data.error === 'You can only update BMI once in a day') {
      bmiError.value = 'You can only update BMI once a day.';
    } else {
      bmiError.value = 'Failed to update BMI.';
    }
  }
}

async function fetchBmiHistory() {
  try {
    const res = await axios.get('/bmi-history');
    bmiHistory.value = res.data.history;
    nextTick(() => renderBmiChart());
  } catch {
    bmiHistory.value = [];
  }
}

async function updateGoal() {
  await axios.post('/user/calorie-goal', { goal: calorieGoal.value })
  goalSuccess.value = 'Goal updated!'
  setTimeout(() => window.location.reload(), 800)
}

const recentWorkouts = ref([]);
const recentLoading = ref(true);
const bmiHistory = ref([]);
const bmiChartRef = ref(null);
const stepHistory = ref([]);
const stepChartRef = ref(null);
const waterHistory = ref([]);
const waterChartRef = ref(null);
const calorieChartRef = ref(null);

onMounted(async () => {
  try {
    const res = await axios.get('/recent-activity');
    recentWorkouts.value = res.data.workouts;
  } catch {
    recentWorkouts.value = [];
  } finally {
    recentLoading.value = false;
  }

  await fetchBmiHistory();
  await fetchStepHistory();
  await fetchWaterHistory();
});

const DHAKA_TZ = 'Asia/Dhaka';

function formatDhaka(date, fmt) {
  const zoned = toZonedTime(date, DHAKA_TZ);
  return tzFormat(zoned, fmt, { timeZone: DHAKA_TZ });
}

function getLast7Days() {
  const days = [];
  for (let i = 6; i >= 0; i--) {
    const d = subDays(new Date(), i);
    days.push(formatDhaka(d, 'yyyy-MM-dd'));
  }
  return days;
}


function getStepChartData() {
  const last7 = getLast7Days();
  const historyMap = Object.fromEntries(stepHistory.value.map(h => [h.date, h.steps]));
  return last7.map(date => ({
    date,
    steps: historyMap[date] || 0,
    weekday: formatDhaka(parseISO(date), 'EEE'),
  }));
}

function getLast7DaysWater() {
  const days = [];
  for (let i = 6; i >= 0; i--) {
    const d = subDays(new Date(), i);
    days.push(formatDhaka(d, 'yyyy-MM-dd'));
  }
  return days;
}

function getWaterChartData() {
  const last7 = getLast7DaysWater();
  const historyMap = Object.fromEntries(waterHistory.value.map(h => [h.date, h.amount]));
  return last7.map(date => ({
    date,
    amount: historyMap[date] || 0,
    weekday: formatDhaka(parseISO(date), 'EEE'),
  }));
}

function renderStepChart() {
  if (!stepChartRef.value) return;
  const chartData = getStepChartData();
  const ctx = stepChartRef.value.getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: chartData.map(d => d.weekday),
      datasets: [{
        label: 'Steps',
        data: chartData.map(d => d.steps),
        backgroundColor: '#fbbf24',
        borderColor: '#f59e42',
        borderWidth: 2,
      }],
    },
    options: {
      scales: {
        x: { title: { display: true, text: 'Day' } },
        y: { title: { display: true, text: 'Steps' }, beginAtZero: true },
      },
      plugins: {
        legend: { display: false },
      },
      responsive: true,
      maintainAspectRatio: false,
    },
  });
}

function renderWaterChart() {
  if (!waterChartRef.value) return;
  const chartData = getWaterChartData();
  const ctx = waterChartRef.value.getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: chartData.map(d => d.weekday),
      datasets: [{
        label: 'Water Intake (ml)',
        data: chartData.map(d => d.amount),
        backgroundColor: '#60a5fa',
        borderColor: '#2563eb',
        borderWidth: 2,
      }],
    },
    options: {
      scales: {
        x: { title: { display: true, text: 'Day' } },
        y: { title: { display: true, text: 'Water Intake (ml)' }, beginAtZero: true },
      },
      plugins: {
        legend: { display: false },
      },
      responsive: true,
      maintainAspectRatio: false,
    },
  });
}

function renderCalorieChart() {
  if (!calorieChartRef.value) return
  if (!props.calorieChart || props.calorieChart.length === 0) return

  const ctx = calorieChartRef.value.getContext('2d')

  const labels = props.calorieChart.map(d =>
    new Date(d.date).toLocaleDateString('en-US', { weekday: 'short' })
  )

  const data = props.calorieChart.map(d => d.calories)

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [{
        label: 'Calories',
        data,
        backgroundColor: '#34d399',
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } }
    }
  })
}



const showStepsModal = ref(false);
const stepsInput = ref('');
const stepsError = ref('');
const stepsSuccess = ref('');
const todaySteps = ref(0);

async function fetchTodaySteps() {
  try {
    const res = await axios.get('/steps/today');
    todaySteps.value = res.data.steps;
  } catch {
    todaySteps.value = 0;
  }
}

async function submitSteps() {
  stepsError.value = '';
  stepsSuccess.value = '';
  const steps = parseInt(stepsInput.value);
  if (!steps || steps < 1) {
    stepsError.value = 'Please enter a valid step count.';
    return;
  }
  try {
    await axios.post('/steps', { steps });
    stepsSuccess.value = 'Steps updated!';
    stepsInput.value = '';
    fetchTodaySteps();
    showStepsModal.value = false;
    await fetchStepHistory(); // Refresh chart data
  } catch {
    stepsError.value = 'Failed to update steps.';
  }
}

async function fetchStepHistory() {
  try {
    const res = await axios.get('/steps-history');
    stepHistory.value = res.data.history;
    nextTick(() => renderStepChart());
  } catch {
    stepHistory.value = [];
  }
}

const showWaterModal = ref(false);
const waterInput = ref('');
const waterError = ref('');
const waterSuccess = ref('');
const todayWater = ref(0);

async function fetchTodayWater() {
  try {
    const res = await axios.get('/water/today');
    todayWater.value = res.data.amount;
  } catch {
    todayWater.value = 0;
  }
}

async function submitWater() {
  waterError.value = '';
  waterSuccess.value = '';
  const amount = parseInt(waterInput.value);
  if (!amount || amount < 1) {
    waterError.value = 'Please enter a valid amount (ml).';
    return;
  }
  try {
    await axios.post('/water', { amount });
    waterSuccess.value = 'Water intake updated!';
    waterInput.value = '';
    fetchTodayWater();
    showWaterModal.value = false;
    await fetchWaterHistory(); // Refresh chart data
  } catch {
    waterError.value = 'Failed to update water intake.';
  }
}

async function fetchWaterHistory() {
  try {
    const res = await axios.get('/water-history');
    waterHistory.value = res.data.history;
    nextTick(() => renderWaterChart());
  } catch {
    waterHistory.value = [];
  }
}

onMounted(() => {
  fetchTodaySteps();
  fetchTodayWater();
  renderCalorieChart(); //
});

function getBmiChartData() {
  const last7 = getLast7Days();
  const historyMap = Object.fromEntries(bmiHistory.value.map(h => [h.date, h.bmi]));
  return last7.map(date => ({
    date,
    bmi: historyMap[date] || 0,
    weekday: formatDhaka(parseISO(date), 'EEE'),
  }));
}

function renderBmiChart() {
  if (!bmiChartRef.value) return;
  const chartData = getBmiChartData();
  const ctx = bmiChartRef.value.getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: chartData.map(d => d.weekday), // x-axis: weekday names
      datasets: [{
        label: 'BMI Value',
        data: chartData.map(d => d.bmi), // y-axis: BMI values
        backgroundColor: '#fbbf24',
        borderColor: '#b45309',
        borderWidth: 2,
      }],
    },
    options: {
      scales: {
        x: { title: { display: true, text: 'Day' } },
        y: { title: { display: true, text: 'BMI Value' }, beginAtZero: true },
      },
      plugins: {
        legend: { display: false },
      },
      responsive: true,
      maintainAspectRatio: false,
    },
  });
}
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- 🔥 Meal Streak & ⭐ Meal Points (Top Right Pills) -->
        <div class="flex justify-end mb-4">
          <div class="flex items-center gap-2">
            <!-- Water Intake -->
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 text-lg font-semibold shadow">
              💧 Today's Water: <span>{{ todayWater }} ml</span>
            </div>
            <!-- Steps -->
            <div class="flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 text-lg font-semibold shadow">
              🚶‍♂️ Today's Steps: <span>{{ todaySteps }}</span>
            </div>
            <!-- Meal Streak -->
            <div
              class="flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-700 dark:text-emerald-300 text-sm font-semibold shadow"
              title="Meal Streak"
            >
              🔥
              <span>{{ stats.meal_streak }}</span>
              <span class="hidden sm:inline">day streak</span>
            </div>

            <!-- Meal Points -->
            <div
              class="flex items-center gap-1 px-3 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-300 text-sm font-semibold shadow"
              title="Meal Points"
            >
              ⭐
              <span>{{ stats.meal_points }}</span>
              <span class="hidden sm:inline">pts</span>
            </div>
          </div>
        </div>

        <!-- Welcome Section -->
        <div
          class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-8 mb-8"
        >
          <h1 class="text-4xl font-bold text-white mb-2">
            Welcome back, {{ user.nickname ? user.nickname : user.name }}! 💪
          </h1>
          <p class="text-indigo-100">Member since {{ stats.joined_date }}</p>
          <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white bg-opacity-10 rounded-lg p-4">
              <span class="block text-indigo-200 text-sm">Age</span>
              <span class="block text-white text-lg font-semibold">{{ user.age || '—' }}</span>
            </div>
            <div class="bg-white bg-opacity-10 rounded-lg p-4">
              <span class="block text-indigo-200 text-sm">Gender</span>
              <span class="block text-white text-lg font-semibold">
                {{ user.gender === 'male' ? 'Male' : user.gender === 'female' ? 'Female' : '—' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Workouts Card -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                  Total Workouts
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.total_workouts }}
                </p>
              </div>

              <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-full">
                <svg
                  class="w-8 h-8 text-blue-600 dark:text-blue-300"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M13 6a1 1 0 11-2 0 1 1 0 012 0zM13 12a1 1 0 11-2 0 1 1 0 012 0zM13 18a1 1 0 11-2 0 1 1 0 012 0zM5 8a2 2 0 11-4 0 2 2 0 014 0zM19 8a2 2 0 11-4 0 2 2 0 014 0zM5 14a2 2 0 11-4 0 2 2 0 014 0zM19 14a2 2 0 11-4 0 2 2 0 014 0zM5 20a2 2 0 11-4 0 2 2 0 014 0zM19 20a2 2 0 11-4 0 2 2 0 014 0z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <!-- Total Calories Card -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                  Calories Consumed
                </p>

                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.total_calories }} kcal
                </p>

                <p class="text-sm text-green-600 mt-1">
                  Today: {{ stats.today_calories }} kcal
                </p>

                <!-- 🔥 GOAL PROGRESS BAR (THIS IS THE NEW PART) -->
                <div class="mt-3">
                  <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                    <span>Goal: {{ stats.daily_goal }} kcal</span>
                    <span>{{ stats.goal_percent }}%</span>
                  </div>

                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                      class="bg-green-500 h-2 rounded-full transition-all"
                      :style="{ width: Math.min(stats.goal_percent, 100) + '%' }"
                    ></div>
                  </div>
                </div>
                <!-- 🔥 END GOAL UI -->

              </div>

              <div class="bg-orange-100 dark:bg-orange-900 p-4 rounded-full ml-4">
                <svg
                  class="w-8 h-8 text-orange-600 dark:text-orange-300"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"
                  />
                </svg>
              </div>
            </div>
          </div>


          <!-- Current Month Card -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                  Current Month
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.this_month.split(' ')[0] }}
                </p>
              </div>

              <div class="bg-green-100 dark:bg-green-900 p-4 rounded-full">
                <svg
                  class="w-8 h-8 text-green-600 dark:text-green-300"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <!-- Goals Card -->
          <div
            class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition"
          >
            <div class="flex items-center justify-between">
              <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                  Goals Completed
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.goals_completed }}/1
                </p>
              </div>

              <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-full">
                <svg
                  class="w-8 h-8 text-purple-600 dark:text-purple-300"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                  />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Recent Activity -->
          <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Recent Activity
            </h2>

            <div class="space-y-4 max-h-64 overflow-y-auto pr-2">
              <template v-if="recentLoading">
                <div class="text-gray-500">Loading...</div>
              </template>
              <template v-else>
                <template v-if="recentWorkouts.length === 0">
                  <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                      </svg>
                    </div>
                    <div class="ml-4 flex-1">
                      <p class="text-gray-900 dark:text-white font-medium">No activity yet</p>
                      <p class="text-gray-500 dark:text-gray-400 text-sm">Start logging your workouts!</p>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div v-for="workout in recentWorkouts" :key="workout.id" class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 6v6l4 2" stroke="#fff" stroke-width="2" fill="none"/>
                      </svg>
                    </div>
                    <div class="ml-4 flex-1">
                      <p class="text-gray-900 dark:text-white font-medium">{{ workout.name }}</p>
                      <p class="text-gray-500 dark:text-gray-400 text-sm">{{ workout.date }} {{ workout.time }} | Sets: {{ workout.sets }}, Reps: {{ workout.reps }}</p>
                    </div>
                  </div>
                </template>
              </template>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
              Quick Actions
            </h2>

            <div class="space-y-3">
              <button
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition"
                @click="goToWorkout"
              >
                + Log Workout
              </button>

              <button
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition"
                @click="goToMeals"
              >
                + Track Meal
              </button>

              <button
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition"
              >
                📊 View Stats
              </button>

              <button
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition hidden"
              >
                ⚙️ Settings
              </button>

              <button
                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-medium py-2 px-4 rounded-lg transition"
                @click="showBmiModal = true"
              >
                🧮 Update BMI
              </button>

              <button
                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition"
                @click="showStepsModal = true"
              >
                🚶‍♂️ Update Steps
              </button>

              <button
                class="w-full bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg transition"
                @click="showWaterModal = true"
              >
                💧 Update Water
              </button>

              <button
                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-lg"
                @click="showGoalModal = true"
              >
                🎯 Set Calorie Goal
              </button>

              <button
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition"
                @click="goToExport"
              >
                📤 Export Health Data
              </button>


            </div>
          </div>

          <!-- BMI History Chart -->
          <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">BMI History</h2>
            <div style="height:300px;">
              <canvas ref="bmiChartRef"></canvas>
            </div>
          </div>

          <!-- Steps History Chart -->
          <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-yellow-700 dark:text-yellow-300 mb-4">Step History</h2>
            <div style="height:300px;">
              <canvas ref="stepChartRef"></canvas>
            </div>
          </div>

          <!-- Water Intake Chart -->
          <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-teal-700 dark:text-teal-300 mb-4">Water Intake</h2>
            <div style="height:300px;">
              <!-- Placeholder for water intake chart -->
              <canvas ref="waterChartRef"></canvas>
            </div>
          </div>
        </div>

          <!-- 🔥 Calorie Intake Chart -->
          <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-green-700 dark:text-green-300 mb-4">
              Calorie Intake (Last 7 Days)
            </h2>
            <div style="height:300px;">
              <canvas ref="calorieChartRef"></canvas>
            </div>
          </div>


        <!-- BMI Modal -->
        <teleport to="body">
          <div v-if="showBmiModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-8 w-full max-w-md relative">
              <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" @click="showBmiModal = false">&times;</button>
              <h2 class="text-xl font-semibold mb-4 text-pink-700 dark:text-pink-300">Update BMI</h2>
              <form @submit.prevent="submitBmi">
                <div class="mb-4">
                  <label class="block text-gray-700 dark:text-gray-200 mb-1">Height (meters)</label>
                  <input v-model="height" type="number" step="0.01" min="0" class="w-full border rounded px-3 py-2" required />
                </div>
                <div class="mb-4">
                  <label class="block text-gray-700 dark:text-gray-200 mb-1">Weight (kg)</label>
                  <input v-model="weight" type="number" step="0.1" min="0" class="w-full border rounded px-3 py-2" required />
                </div>
                <div v-if="bmi !== null" class="mb-2 text-green-700 dark:text-green-300">Your BMI: {{ bmi }}</div>
                <div v-if="bmiStatus" class="mb-2 font-semibold text-indigo-700 dark:text-indigo-300">Status: {{ bmiStatus }}</div>
                <div v-if="bmiMessage" class="mb-2 italic text-blue-700 dark:text-blue-300">{{ bmiMessage }}</div>
                <div v-if="bmiError" class="mb-2 text-red-600">{{ bmiError }}</div>
                <div v-if="bmiSuccess" class="mb-2 text-green-600">{{ bmiSuccess }}</div>
                <button type="submit" class="bg-pink-600 hover:bg-pink-700 text-white font-medium py-2 px-4 rounded-lg w-full">Update</button>
              </form>
            </div>
          </div>
        </teleport>

        <!-- Steps Modal -->
        <teleport to="body">
          <div v-if="showStepsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-8 w-full max-w-md relative">
              <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" @click="showStepsModal = false">&times;</button>
              <h2 class="text-xl font-semibold mb-4 text-yellow-700 dark:text-yellow-300">Update Steps</h2>
              <form @submit.prevent="submitSteps">
                <div class="mb-4">
                  <label class="block text-gray-700 dark:text-gray-200 mb-1">Steps</label>
                  <input v-model="stepsInput" type="number" min="1" class="w-full border rounded px-3 py-2" required />
                </div>
                <div v-if="stepsError" class="mb-2 text-red-600">{{ stepsError }}</div>
                <div v-if="stepsSuccess" class="mb-2 text-green-600">{{ stepsSuccess }}</div>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg w-full">Update</button>
              </form>
            </div>
          </div>
        </teleport>

        <!-- Water Intake Modal -->
        <teleport to="body">
          <div v-if="showWaterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-8 w-full max-w-md relative">
              <button class="absolute top-2 right-2 text-gray-400 hover:text-gray-600" @click="showWaterModal = false">&times;</button>
              <h2 class="text-xl font-semibold mb-4 text-teal-700 dark:text-teal-300">Update Water Intake</h2>
              <form @submit.prevent="submitWater">
                <div class="mb-4">
                  <label class="block text-gray-700 dark:text-gray-200 mb-1">Amount (ml)</label>
                  <input v-model="waterInput" type="number" min="1" class="w-full border rounded px-3 py-2" required />
                </div>
                <div v-if="waterError" class="mb-2 text-red-600">{{ waterError }}</div>
                <div v-if="waterSuccess" class="mb-2 text-green-600">{{ waterSuccess }}</div>
                <button type="submit" class="bg-teal-600 hover:bg-teal-700 text-white font-medium py-2 px-4 rounded-lg w-full">Update</button>
              </form>
            </div>
          </div>
        </teleport>

        <!-- 🎯 Calorie Goal Modal -->
        <teleport to="body">
          <div v-if="showGoalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-8 w-full max-w-md relative">

              <button
                type="button"
                class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                @click="showGoalModal = false"
              >
                Close
              </button>

              <h2 class="text-xl font-semibold mb-4 text-orange-600 dark:text-orange-300">
                Set Daily Calorie Goal
              </h2>

              <input
                v-model="calorieGoal"
                type="number"
                min="1000"
                max="5000"
                class="w-full border rounded px-4 py-2 mb-4 dark:bg-gray-800 dark:text-white"
              />

              <div v-if="goalSuccess" class="text-green-600 mb-2">
                {{ goalSuccess }}
              </div>

              <button
                type="button"
                @click="updateGoal"
                class="bg-orange-600 hover:bg-orange-700 text-white w-full py-2 rounded-lg"
              >
                Save Goal
              </button>

            </div>
          </div>
        </teleport>

      </div>
    </div>
  </AppLayout>
</template>


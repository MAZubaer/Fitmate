<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
  user: Object,
  stats: Object,
})
</script>

<template>
  <AppLayout>
    <Head title="Dashboard" />

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- 🔥 Meal Streak & ⭐ Meal Points (Top Right Pills) -->
        <div class="flex justify-end mb-4">
          <div class="flex items-center gap-2">
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
              <div>
                <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                  Calories Burned
                </p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                  {{ stats.total_calories }}
                </p>
              </div>

              <div class="bg-orange-100 dark:bg-orange-900 p-4 rounded-full">
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
                  0/5
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

            <div class="space-y-4">
              <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div
                  class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center"
                >
                  <svg
                    class="w-6 h-6 text-blue-600 dark:text-blue-300"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                    />
                  </svg>
                </div>

                <div class="ml-4 flex-1">
                  <p class="text-gray-900 dark:text-white font-medium">No activity yet</p>
                  <p class="text-gray-500 dark:text-gray-400 text-sm">
                    Start logging your workouts!
                  </p>
                </div>
              </div>
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
              >
                + Log Workout
              </button>

              <button
                class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition"
              >
                + Track Meal
              </button>

              <button
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition"
              >
                📊 View Stats
              </button>

              <button
                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition"
              >
                ⚙️ Settings
              </button>
            </div>
          </div>
        </div>

        <!-- Progress Section -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            Weekly Progress
          </h2>

          <div class="space-y-4">
            <div>
              <div class="flex justify-between mb-2">
                <span class="text-gray-700 dark:text-gray-300 font-medium">Monday</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">0/5 workouts</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between mb-2">
                <span class="text-gray-700 dark:text-gray-300 font-medium">Tuesday</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">0/5 workouts</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between mb-2">
                <span class="text-gray-700 dark:text-gray-300 font-medium">Wednesday</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">0/5 workouts</span>
              </div>
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  layout: AppLayout,
}
</script>

<script setup>
import { ref } from 'vue'

const from = ref('')
const to = ref('')

const download = (type) => {
  if (!from.value || !to.value) {
    alert('Please select both dates')
    return
  }

  window.location = `/analytics/export/${type}?from=${from.value}&to=${to.value}`
}
</script>

<template>
  <div class="max-w-5xl mx-auto py-10 px-6">

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg p-8">
      <h1 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white flex items-center gap-2">
        📤 Export Health Analytics
      </h1>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
            From
          </label>
          <input
            type="date"
            v-model="from"
            class="w-full px-4 py-2 rounded-lg border dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>

        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
            To
          </label>
          <input
            type="date"
            v-model="to"
            class="w-full px-4 py-2 rounded-lg border dark:bg-gray-800 dark:border-gray-700 dark:text-white"
          />
        </div>
      </div>

      <div class="flex gap-4">
        <button
          @click="download('csv')"
          class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg shadow font-medium"
        >
          📄 Download CSV
        </button>

        <button
          @click="download('pdf')"
          class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow font-medium"
        >
          🧾 Download PDF
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const notifications = ref([])
const loading = ref(true)

const fetchNotifications = async () => {
    const res = await axios.get('/api/notifications')
    notifications.value = res.data
    loading.value = false
}

const markRead = async (notification) => {
    await axios.post(`/api/notifications/${notification.id}/read`)
    notification.read = true
}

onMounted(fetchNotifications)
</script>

<template>
<AppLayout>
<Head title="Notifications" />

<div class="py-12">
  <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white rounded-xl shadow-lg p-8">

      <h1 class="text-3xl font-bold text-[#1B3C53] mb-6 flex items-center gap-2">
        🔔 Notifications
      </h1>

      <div v-if="loading" class="text-gray-500">
        Loading notifications...
      </div>

      <div v-else-if="notifications.length === 0" class="text-gray-500">
        No notifications yet. Add a meal or workout with a time ⏰
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="n in notifications"
          :key="n.id"
          @click="markRead(n)"
          class="p-4 rounded-lg shadow-sm cursor-pointer flex justify-between items-center transition"
          :class="n.read ? 'bg-gray-100' : 'bg-yellow-100 hover:bg-yellow-200'"
        >
          <div>
            <p class="font-medium text-gray-900">
              {{ n.message }}
            </p>
            <p class="text-sm text-gray-600">
              {{ new Date(n.scheduled_at).toLocaleString() }}
            </p>
          </div>

          <span v-if="!n.read" class="text-red-600 text-xs font-bold">
            NEW
          </span>
        </div>
      </div>

    </div>
  </div>
</div>

</AppLayout>
</template>

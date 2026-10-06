<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { marked } from 'marked'
import DOMPurify from 'dompurify'

const input = ref('')
const chats = ref([])
const messages = ref([])
const loading = ref(false)
const sessionId = ref(null)
const chatContainer = ref(null)

const STORAGE_KEY = 'fitmate_ai_last_session'

const renderMarkdown = (text) => DOMPurify.sanitize(marked.parse(text || '', {
  breaks: true,
  gfm: true,
}))

const getScrollParent = (el) => {
  while (el) {
    if (el.scrollHeight > el.clientHeight) return el
    el = el.parentElement
  }
  return document.documentElement
}

const scrollToBottom = async () => {
  await nextTick()
  if (!chatContainer.value) return
  const container = getScrollParent(chatContainer.value)
  container.scrollTop = container.scrollHeight
}

const loadHistory = async () => {
  const res = await axios.get('/ai/chat-history')
  chats.value = res.data
}

const loadChat = async (session) => {
  sessionId.value = session
  localStorage.setItem(STORAGE_KEY, session)

  const res = await axios.get('/ai/chat/' + session)
  messages.value = res.data.flatMap(chat => [
    { role: 'user', text: chat.user_message },
    { role: 'ai', text: chat.ai_reply }
  ])

  await scrollToBottom()
}

const newChat = () => {
  sessionId.value = null
  messages.value = []
  localStorage.removeItem(STORAGE_KEY)
  scrollToBottom()
}

const deleteChat = async (session) => {
  if (!confirm('Delete this chat?')) return

  await axios.delete('/ai/chat/' + session)

  chats.value = chats.value.filter(c => c.session_id !== session)

  if (sessionId.value === session) {
    sessionId.value = null
    messages.value = []
    localStorage.removeItem(STORAGE_KEY)
  }
}

const sendMessage = async () => {
  if (!input.value.trim()) return

  if (!sessionId.value) {
    sessionId.value = crypto.randomUUID()
    localStorage.setItem(STORAGE_KEY, sessionId.value)
  }

  const userMsg = input.value
  messages.value.push({ role: 'user', text: userMsg })
  input.value = ''
  loading.value = true
  await scrollToBottom()

  try {
    const res = await axios.post('/ai/meal-assistant', {
      prompt: userMsg,
      session_id: sessionId.value
    })

    sessionId.value = res.data.session_id
    localStorage.setItem(STORAGE_KEY, sessionId.value)

    messages.value.push({ role: 'ai', text: res.data.reply })

    if (chats.value.every(c => c.session_id !== sessionId.value)) {
      chats.value.unshift({
        session_id: sessionId.value,
        user_message: userMsg,
        created_at: new Date().toISOString()
      })
    }

    await scrollToBottom()

  } catch {
    messages.value.push({
      role: 'ai',
      text: 'AI coach is unavailable right now.'
    })
  }

  loading.value = false
  await scrollToBottom()
}

onMounted(async () => {
  await loadHistory()

  const last = localStorage.getItem(STORAGE_KEY)
  if (last && chats.value.some(c => c.session_id === last)) {
    await loadChat(last)
  } else {
    newChat()
  }
})
</script>

<template>
<AppLayout>
  <div class="min-h-screen bg-gradient-to-br from-[#0F2027] via-[#203A43] to-[#2C5364] flex">

    <!-- Sidebar -->
    <div class="w-80 bg-black/30 backdrop-blur-xl border-r border-white/10 text-white flex flex-col">
      <div class="px-6 py-5 border-b border-white/10">
        <h2 class="text-lg font-semibold">💬 Chats</h2>
        <button @click="newChat"
          class="mt-3 w-full bg-[#4FD1C5] text-[#0F2027] py-2 rounded-lg font-medium hover:bg-[#38B2AC]">
          + New Chat
        </button>
      </div>

      <div class="flex-1 overflow-y-auto">
        <div
          v-for="chat in chats"
          :key="chat.session_id"
          class="group flex items-center justify-between px-5 py-4 border-b border-white/5 hover:bg-white/5 transition"
        >
          <div
            class="flex-1 cursor-pointer"
            @click="loadChat(chat.session_id)"
          >
            <div class="text-sm truncate">{{ chat.user_message }}</div>
            <div class="text-xs text-white/40 mt-1">
              {{ new Date(chat.created_at).toLocaleString() }}
            </div>
          </div>

          <button
            @click.stop="deleteChat(chat.session_id)"
            class="opacity-0 group-hover:opacity-100 transition text-red-400 hover:text-red-600 text-sm ml-2"
          >
            🗑
          </button>
        </div>
      </div>
    </div>

    <!-- Chat -->
    <div class="flex-1 flex flex-col">

      <div class="px-10 py-6 border-b border-white/10 text-white">
        <h1 class="text-2xl font-semibold">FitMate AI Coach</h1>
        <p class="text-sm text-white/60">Your personal fitness assistant</p>
      </div>

      <div ref="chatContainer" class="flex-1 overflow-y-auto px-10 py-8 space-y-6">

        <div v-for="(msg, i) in messages" :key="i"
          class="flex"
          :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">

          <div
            class="max-w-[70%] px-5 py-4 rounded-2xl text-sm leading-relaxed"
            :class="msg.role === 'user'
              ? 'bg-[#1B3C53] text-white'
              : 'bg-white/10 text-white border border-white/10'">
            <div v-if="msg.role === 'ai'" class="ai-markdown" v-html="renderMarkdown(msg.text)"></div>
            <span v-else>{{ msg.text }}</span>
          </div>
        </div>

        <div v-if="loading" class="text-white/50 italic">
          AI is thinking…
        </div>

        <div v-if="messages.length === 0 && !loading"
          class="text-center text-white/40 mt-24 text-sm">
          Ask anything about workouts, meals, health, or fitness 💪
        </div>
      </div>

      <div class="px-8 py-6 border-t border-white/10 bg-black/20 flex gap-4">
        <input
          v-model="input"
          @keyup.enter="sendMessage"
          placeholder="Ask your FitMate AI..."
          class="flex-1 bg-black/30 text-white placeholder-white/40 border border-white/10 rounded-xl px-5 py-4 focus:outline-none focus:ring-2 focus:ring-[#4FD1C5]" />

        <button
          @click="sendMessage"
          :disabled="loading"
          class="px-8 py-4 rounded-xl bg-[#4FD1C5] text-[#0F2027] font-medium hover:bg-[#38B2AC]">
          Ask
        </button>
      </div>

    </div>
  </div>
</AppLayout>
</template>

<style scoped>
.ai-markdown :deep(h1),
.ai-markdown :deep(h2),
.ai-markdown :deep(h3) {
  margin: 0.9rem 0 0.45rem;
  font-weight: 700;
  line-height: 1.3;
}

.ai-markdown :deep(h1) {
  font-size: 1.15rem;
}

.ai-markdown :deep(h2) {
  font-size: 1.05rem;
}

.ai-markdown :deep(h3) {
  font-size: 0.95rem;
}

.ai-markdown :deep(p) {
  margin: 0.55rem 0;
}

.ai-markdown :deep(ul),
.ai-markdown :deep(ol) {
  margin: 0.55rem 0;
  padding-left: 1.35rem;
}

.ai-markdown :deep(ul) {
  list-style: disc;
}

.ai-markdown :deep(ol) {
  list-style: decimal;
}

.ai-markdown :deep(li) {
  margin: 0.25rem 0;
}

.ai-markdown :deep(strong) {
  color: #99f6e4;
  font-weight: 700;
}

.ai-markdown :deep(table) {
  display: block;
  max-width: 100%;
  margin: 0.75rem 0;
  overflow-x: auto;
  border-collapse: collapse;
  white-space: nowrap;
}

.ai-markdown :deep(th),
.ai-markdown :deep(td) {
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 0.45rem 0.65rem;
  text-align: left;
}

.ai-markdown :deep(th) {
  background: rgba(79, 209, 197, 0.16);
  font-weight: 700;
}

.ai-markdown :deep(code) {
  border-radius: 0.25rem;
  background: rgba(0, 0, 0, 0.25);
  padding: 0.12rem 0.3rem;
  font-size: 0.85em;
}

.ai-markdown :deep(pre) {
  margin: 0.75rem 0;
  overflow-x: auto;
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.3);
  padding: 0.75rem;
}

.ai-markdown :deep(pre code) {
  background: transparent;
  padding: 0;
}

.ai-markdown :deep(blockquote) {
  margin: 0.75rem 0;
  border-left: 3px solid #4fd1c5;
  padding-left: 0.75rem;
  color: rgba(255, 255, 255, 0.75);
}
</style>

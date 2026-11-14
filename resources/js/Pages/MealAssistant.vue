<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import Icon from '@/Components/Icon.vue';
import { toast } from '@/composables/useToast';
import { timeAgo } from '@/utils/format';

const STORAGE_KEY = 'fitmate_ai_last_session';

const input = ref('');
const chats = ref([]);
const messages = ref([]);
const loading = ref(false);
const historyLoading = ref(true);
const sessionId = ref(null);
const scroller = ref(null);
const composer = ref(null);
const historyOpen = ref(false);

const suggestions = [
    { icon: 'utensils', title: 'Plan my meals', prompt: 'Plan a high-protein day of meals that fits my recent calorie intake.' },
    { icon: 'dumbbell', title: 'Review my training', prompt: 'Look at my recent workouts. What should I train next and why?' },
    { icon: 'droplet', title: 'Hydration check', prompt: 'Am I drinking enough water based on my data? How can I improve?' },
    { icon: 'scale', title: 'Explain my BMI', prompt: 'Explain what my BMI means and give me 3 realistic goals for this month.' },
];

const storage = {
    get: () => {
        try { return localStorage.getItem(STORAGE_KEY); } catch { return null; }
    },
    set: (v) => {
        try { localStorage.setItem(STORAGE_KEY, v); } catch { /* ignore */ }
    },
    clear: () => {
        try { localStorage.removeItem(STORAGE_KEY); } catch { /* ignore */ }
    },
};

const renderMarkdown = (text) => DOMPurify.sanitize(marked.parse(text || '', { breaks: true, gfm: true }));

const scrollToBottom = async () => {
    await nextTick();
    if (scroller.value) scroller.value.scrollTo({ top: scroller.value.scrollHeight, behavior: 'smooth' });
};

const autoGrow = () => {
    const el = composer.value;
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = `${Math.min(el.scrollHeight, 180)}px`;
};

async function loadHistory() {
    try {
        chats.value = (await axios.get('/ai/chat-history')).data;
    } catch {
        chats.value = [];
    } finally {
        historyLoading.value = false;
    }
}

async function loadChat(session) {
    sessionId.value = session;
    storage.set(session);
    historyOpen.value = false;
    try {
        const { data } = await axios.get(`/ai/chat/${session}`);
        messages.value = data.flatMap((chat) => [
            { role: 'user', text: chat.user_message },
            { role: 'ai', text: chat.ai_reply },
        ]);
    } catch {
        toast('Could not load that conversation', 'error');
    }
    await scrollToBottom();
}

function newChat() {
    sessionId.value = null;
    messages.value = [];
    storage.clear();
    historyOpen.value = false;
    nextTick(() => composer.value?.focus());
}

const deleting = ref(null);

async function confirmDelete() {
    const session = deleting.value;
    deleting.value = null;
    try {
        await axios.delete(`/ai/chat/${session}`);
        chats.value = chats.value.filter((c) => c.session_id !== session);
        if (sessionId.value === session) newChat();
        toast('Conversation deleted');
    } catch {
        toast('Could not delete the conversation', 'error');
    }
}

async function sendMessage(text = input.value) {
    const prompt = text.trim();
    if (!prompt || loading.value) return;

    if (!sessionId.value) {
        sessionId.value = crypto.randomUUID();
        storage.set(sessionId.value);
    }

    messages.value.push({ role: 'user', text: prompt });
    input.value = '';
    nextTick(autoGrow);
    loading.value = true;
    await scrollToBottom();

    try {
        const { data } = await axios.post('/ai/meal-assistant', { prompt, session_id: sessionId.value });
        sessionId.value = data.session_id;
        storage.set(sessionId.value);
        messages.value.push({ role: 'ai', text: data.reply });

        if (chats.value.every((c) => c.session_id !== sessionId.value)) {
            chats.value.unshift({ session_id: sessionId.value, user_message: prompt, created_at: new Date().toISOString() });
        }
    } catch {
        messages.value.push({ role: 'ai', text: 'Sorry — the AI coach is unavailable right now. Please try again in a moment.', error: true });
    } finally {
        loading.value = false;
        await scrollToBottom();
    }
}

function onKeydown(e) {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
        e.preventDefault();
        sendMessage();
    }
}

onMounted(async () => {
    await loadHistory();
    const last = storage.get();
    if (last && chats.value.some((c) => c.session_id === last)) {
        await loadChat(last);
    } else {
        newChat();
    }
});
</script>

<template>
    <AppLayout full-bleed>
        <Head title="AI Coach" />

        <div class="relative flex h-[calc(100dvh-4rem)] min-h-0">
            <!-- History panel -->
            <div v-if="historyOpen" class="fixed inset-0 z-30 bg-slate-950/40 backdrop-blur-sm md:hidden" @click="historyOpen = false" />
            <aside
                class="absolute inset-y-0 left-0 z-30 flex w-72 max-w-[85vw] flex-col border-r border-line bg-surface transition-transform duration-300 md:static md:z-auto md:translate-x-0"
                :class="historyOpen ? 'translate-x-0 shadow-2xl' : '-translate-x-full'"
            >
                <div class="flex items-center gap-2 p-4">
                    <button class="btn-primary flex-1" @click="newChat"><Icon name="plus" class="h-4 w-4" /> New chat</button>
                    <button class="btn-icon md:hidden" aria-label="Close history" @click="historyOpen = false"><Icon name="x" /></button>
                </div>
                <p class="eyebrow px-5 pb-2">Recent</p>
                <div class="scrollbar-thin flex-1 space-y-0.5 overflow-y-auto px-2 pb-4">
                    <template v-if="historyLoading">
                        <div v-for="i in 5" :key="i" class="space-y-2 px-3 py-2.5"><div class="skeleton h-3.5 w-4/5" /><div class="skeleton h-3 w-1/3" /></div>
                    </template>
                    <p v-else-if="chats.length === 0" class="px-3 py-6 text-center text-sm text-muted">Your conversations will appear here.</p>
                    <template v-else>
                    <div
                        v-for="chat in chats"
                        :key="chat.session_id"
                        class="group relative flex cursor-pointer items-center gap-2 rounded-xl px-3 py-2.5 transition"
                        :class="chat.session_id === sessionId ? 'bg-brand-500/10' : 'hover:bg-subtle'"
                        @click="loadChat(chat.session_id)"
                    >
                        <Icon name="message" class="h-4 w-4 shrink-0" :class="chat.session_id === sessionId ? 'text-brand-600 dark:text-brand-400' : 'text-muted'" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ chat.user_message }}</p>
                            <p class="text-xs text-muted">{{ timeAgo(chat.created_at) }}</p>
                        </div>
                        <button
                            class="btn-icon h-7 w-7 opacity-100 hover:text-rose-600 md:opacity-0 md:group-hover:opacity-100"
                            aria-label="Delete conversation"
                            @click.stop="deleting = chat.session_id"
                        >
                            <Icon name="trash" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    </template>
                </div>
            </aside>

            <!-- Conversation -->
            <section class="flex min-w-0 flex-1 flex-col">
                <header class="flex h-14 shrink-0 items-center gap-3 border-b border-line px-4 sm:px-6">
                    <button class="btn-icon -ml-2 md:hidden" aria-label="Show conversations" @click="historyOpen = true"><Icon name="history" /></button>
                    <span class="grid h-8 w-8 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-teal-600 text-white">
                        <Icon name="sparkles" class="h-4 w-4" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold">FitMate Coach</p>
                        <p class="truncate text-xs text-muted">Uses your meals, workouts, steps, water and BMI</p>
                    </div>
                    <button class="btn-icon md:hidden" aria-label="New chat" @click="newChat"><Icon name="plus" /></button>
                </header>

                <div ref="scroller" class="scrollbar-thin min-h-0 flex-1 overflow-y-auto">
                    <div class="mx-auto w-full max-w-3xl px-4 py-6 sm:px-6 sm:py-8">
                        <!-- Empty state -->
                        <div v-if="messages.length === 0 && !loading" class="flex flex-col items-center pt-6 text-center sm:pt-12">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-gradient-to-br from-brand-400 to-teal-600 text-white shadow-lg shadow-brand-600/25">
                                <Icon name="sparkles" class="h-7 w-7" />
                            </span>
                            <h1 class="mt-5 text-2xl font-bold tracking-tight sm:text-3xl">How can I help you today?</h1>
                            <p class="mt-2 max-w-md text-sm text-muted">Ask about workouts, nutrition, recovery or hydration. I'll tailor advice to what you've logged.</p>
                            <div class="mt-8 grid w-full gap-3 sm:grid-cols-2">
                                <button
                                    v-for="s in suggestions"
                                    :key="s.title"
                                    class="card group flex items-start gap-3 p-4 text-left transition hover:-translate-y-0.5 hover:border-brand-500/40 hover:shadow-lift"
                                    @click="sendMessage(s.prompt)"
                                >
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-subtle text-muted transition group-hover:bg-brand-500/10 group-hover:text-brand-600 dark:group-hover:text-brand-400">
                                        <Icon :name="s.icon" class="h-[18px] w-[18px]" />
                                    </span>
                                    <span>
                                        <span class="block text-sm font-semibold">{{ s.title }}</span>
                                        <span class="mt-0.5 block text-xs text-muted">{{ s.prompt }}</span>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="space-y-6">
                            <div v-for="(msg, i) in messages" :key="i" class="flex gap-3 animate-fade-up" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                                <span v-if="msg.role === 'ai'" class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-teal-600 text-white">
                                    <Icon name="sparkles" class="h-4 w-4" />
                                </span>
                                <div
                                    v-if="msg.role === 'user'"
                                    class="max-w-[85%] whitespace-pre-wrap rounded-2xl rounded-tr-md bg-brand-600 px-4 py-2.5 text-sm leading-relaxed text-white sm:max-w-[75%]"
                                >{{ msg.text }}</div>
                                <div
                                    v-else
                                    class="ai-markdown min-w-0 max-w-[90%] text-sm leading-relaxed sm:max-w-[85%]"
                                    :class="msg.error ? 'rounded-2xl border border-rose-500/30 bg-rose-500/5 px-4 py-3 text-rose-700 dark:text-rose-300' : 'pt-1'"
                                    v-html="renderMarkdown(msg.text)"
                                />
                            </div>

                            <div v-if="loading" class="flex gap-3">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-teal-600 text-white">
                                    <Icon name="sparkles" class="h-4 w-4" />
                                </span>
                                <div class="flex items-center gap-1.5 rounded-2xl bg-subtle px-4 py-3" aria-label="Coach is typing">
                                    <span class="h-2 w-2 animate-pulse-dot rounded-full bg-muted" />
                                    <span class="h-2 w-2 animate-pulse-dot rounded-full bg-muted [animation-delay:0.15s]" />
                                    <span class="h-2 w-2 animate-pulse-dot rounded-full bg-muted [animation-delay:0.3s]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Composer -->
                <div class="pb-safe shrink-0 border-t border-line bg-surface/80 backdrop-blur">
                    <form class="mx-auto w-full max-w-3xl px-4 py-3 sm:px-6 sm:py-4" @submit.prevent="sendMessage()">
                        <div class="flex items-end gap-2 rounded-2xl border border-line bg-surface p-2 shadow-sm transition focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-500/15">
                            <textarea
                                ref="composer"
                                v-model="input"
                                rows="1"
                                maxlength="1000"
                                class="max-h-44 min-h-[2.5rem] flex-1 resize-none border-0 bg-transparent px-2 py-2 text-sm text-ink placeholder:text-muted/70 focus:outline-none focus:ring-0"
                                placeholder="Message your coach…"
                                aria-label="Message"
                                @input="autoGrow"
                                @keydown="onKeydown"
                            />
                            <button
                                type="submit"
                                class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-brand-600 text-white transition hover:bg-brand-700 disabled:bg-subtle disabled:text-muted"
                                :disabled="loading || !input.trim()"
                                aria-label="Send"
                            >
                                <Icon name="arrow-up" class="h-5 w-5" />
                            </button>
                        </div>
                        <p class="mt-2 text-center text-[11px] text-muted">Enter to send · Shift + Enter for a new line · AI can make mistakes and isn't medical advice.</p>
                    </form>
                </div>
            </section>
        </div>

        <ConfirmDialog
            :show="!!deleting"
            title="Delete conversation?"
            message="This conversation will be removed permanently."
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </AppLayout>
</template>

<style scoped>
.ai-markdown :deep(> :first-child) { margin-top: 0; }
.ai-markdown :deep(> :last-child) { margin-bottom: 0; }
.ai-markdown :deep(h1),
.ai-markdown :deep(h2),
.ai-markdown :deep(h3) {
    margin: 1.1rem 0 0.5rem;
    font-weight: 700;
    line-height: 1.3;
}
.ai-markdown :deep(h1) { font-size: 1.15rem; }
.ai-markdown :deep(h2) { font-size: 1.05rem; }
.ai-markdown :deep(h3) { font-size: 0.95rem; }
.ai-markdown :deep(p) { margin: 0.6rem 0; }
.ai-markdown :deep(ul),
.ai-markdown :deep(ol) { margin: 0.6rem 0; padding-left: 1.35rem; }
.ai-markdown :deep(ul) { list-style: disc; }
.ai-markdown :deep(ol) { list-style: decimal; }
.ai-markdown :deep(li) { margin: 0.3rem 0; }
.ai-markdown :deep(li::marker) { color: rgb(var(--muted)); }
.ai-markdown :deep(strong) { font-weight: 700; }
.ai-markdown :deep(a) { color: theme('colors.brand.600'); text-decoration: underline; }
.ai-markdown :deep(hr) { margin: 1rem 0; border-color: rgb(var(--line)); }
.ai-markdown :deep(table) {
    display: block;
    max-width: 100%;
    margin: 0.85rem 0;
    overflow-x: auto;
    border-collapse: collapse;
    font-size: 0.8125rem;
}
.ai-markdown :deep(th),
.ai-markdown :deep(td) {
    border: 1px solid rgb(var(--line));
    padding: 0.5rem 0.7rem;
    text-align: left;
    white-space: nowrap;
}
.ai-markdown :deep(th) { background: rgb(var(--subtle)); font-weight: 600; }
.ai-markdown :deep(code) {
    border-radius: 0.35rem;
    background: rgb(var(--subtle));
    padding: 0.12rem 0.35rem;
    font-size: 0.85em;
}
.ai-markdown :deep(pre) {
    margin: 0.85rem 0;
    overflow-x: auto;
    border-radius: 0.75rem;
    background: rgb(var(--subtle));
    padding: 0.85rem;
}
.ai-markdown :deep(pre code) { background: transparent; padding: 0; }
.ai-markdown :deep(blockquote) {
    margin: 0.85rem 0;
    border-left: 3px solid theme('colors.brand.500');
    padding-left: 0.85rem;
    color: rgb(var(--muted));
}
</style>

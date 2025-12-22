<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, nextTick } from 'vue';
import axios from 'axios';

const prompt = ref('');
const messages = ref([]);
const loading = ref(false);
const error = ref('');
const chatContainer = ref(null);

const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

const askAi = async () => {
    if (!prompt.value.trim()) return;

    messages.value.push({
        role: 'user',
        content: prompt.value,
    });

    const userPrompt = prompt.value;
    prompt.value = '';
    loading.value = true;
    error.value = '';

    scrollToBottom();

    try {
        const res = await axios.post('/ai/meal-assistant', {
            prompt: userPrompt,
        });

        messages.value.push({
            role: 'assistant',
            content: res.data.reply,
        });
    } catch {
        error.value = 'AI coach is unavailable right now.';
    } finally {
        loading.value = false;
        scrollToBottom();
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        askAi();
    }
};
</script>

<template>
    <AppLayout>
        <Head title="AI Meal Assistant" />

        <div class="min-h-screen py-10 bg-gradient-to-br from-[#0F2027] via-[#203A43] to-[#2C5364]">
            <div class="max-w-5xl mx-auto px-6">

                <!-- Main Card -->
                <div class="rounded-3xl backdrop-blur-xl bg-white/5 border border-white/10 shadow-2xl flex flex-col h-[75vh]">

                    <!-- Header -->
                    <div class="px-8 py-6 border-b border-white/10">
                        <h1 class="text-2xl font-semibold text-white tracking-wide">
                            FitMate AI Coach
                        </h1>
                        <p class="text-sm text-white/60 mt-1">
                            Personalized meal guidance powered by AI
                        </p>
                    </div>

                    <!-- Chat -->
                    <div
                        ref="chatContainer"
                        class="flex-1 overflow-y-auto px-8 py-6 space-y-6"
                    >
                        <div
                            v-for="(msg, index) in messages"
                            :key="index"
                            class="flex"
                            :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                        >
                            <div
                                class="max-w-[70%] px-5 py-4 rounded-2xl text-sm leading-relaxed whitespace-pre-line"
                                :class="msg.role === 'user'
                                    ? 'bg-[#1B3C53] text-white rounded-br-md'
                                    : 'bg-white/10 text-white border border-white/10 rounded-bl-md'"
                            >
                                {{ msg.content }}
                            </div>
                        </div>

                        <div v-if="loading" class="flex justify-start">
                            <div class="bg-white/10 border border-white/10 px-5 py-4 rounded-2xl text-white/70 text-sm">
                                Thinking…
                            </div>
                        </div>

                        <div
                            v-if="messages.length === 0 && !loading"
                            class="text-center text-white/40 mt-28 text-sm"
                        >
                            Ask your AI coach about meals, calories, or diet plans
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="px-6 py-5 border-t border-white/10 bg-black/20 rounded-b-3xl">
                        <div class="flex gap-4 items-end">
                            <textarea
                                v-model="prompt"
                                rows="2"
                                placeholder="Type your question…"
                                class="flex-1 bg-black/30 text-white placeholder-white/40 border border-white/10 rounded-xl p-4 resize-none text-sm focus:outline-none focus:ring-2 focus:ring-[#4FD1C5]"
                                @keydown="handleKeydown"
                            ></textarea>

                            <button
                                @click="askAi"
                                :disabled="loading"
                                class="px-6 py-4 rounded-xl bg-[#4FD1C5] text-[#0F2027] font-medium hover:bg-[#38B2AC] transition disabled:opacity-50"
                            >
                                Ask
                            </button>
                        </div>

                        <p v-if="error" class="text-red-400 text-sm mt-2">
                            {{ error }}
                        </p>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

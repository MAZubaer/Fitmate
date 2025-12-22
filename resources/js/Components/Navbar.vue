<script setup>
import { Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const showDropdown = ref(false);
const currentPage = computed(() => usePage().component);

const form = useForm({});

const submitLogout = () => {
    form.post(route('logout'));
};


const navItems = [
    { label: 'Dashboard', route: 'dashboard', icon: '📊' },
    { label: 'Workout', route: 'workout.index', icon: '🏋️' },
    { label: 'Meals', route: 'meals.index', icon: '🍛' },   // <-- ADDED
    { label: 'Meal Assistant', route: 'meal.assistant', icon: '🍽️' },
    { label: 'Notifications', route: 'notifications', icon: '🔔' },
];
</script>

<template>
    <nav class="bg-gradient-to-r from-[#1B3C53] via-[#234C6A] to-[#456882] shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo / Home Button -->
                <Link href="/" class="flex items-center space-x-3 group">
                    <div class="w-12 h-12 bg-black rounded-lg flex items-center justify-center group-hover:scale-110 transition">
                        <span class="text-white font-bold text-lg">FM</span>
                    </div>
                    <span class="text-white font-bold text-xl hidden sm:inline group-hover:text-[#E3E3E3] transition">
                        FitMate
                    </span>
                </Link>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden md:flex items-center space-x-1">
                    <template v-for="item in navItems" :key="item.route">
                        <Link
                            :href="route(item.route)"
                            class="px-4 py-2 rounded-full text-white font-medium text-sm hover:bg-[#E3E3E3] hover:text-[#1B3C53] transition duration-300 flex items-center space-x-2"
                            :class="{
                                'bg-[#E3E3E3] text-[#1B3C53]':
                                    currentPage.includes(item.route.split('.')[0]),
                                'bg-transparent': !currentPage.includes(
                                    item.route.split('.')[0]
                                ),
                            }"
                        >
                            <span>{{ item.icon }}</span>
                            <span>{{ item.label }}</span>
                        </Link>
                    </template>
                </div>

                <!-- Right Side: User Profile & Auth -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile Menu Button -->
                    <button
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-white hover:bg-[#456882] focus:outline-none transition"
                        @click="showDropdown = !showDropdown"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"
                            />
                        </svg>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button
                            class="flex items-center space-x-2 px-4 py-2 rounded-full bg-[#456882] hover:bg-[#E3E3E3] hover:text-[#1B3C53] text-white font-medium transition duration-300"
                        >
                            <div
                                class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-[#1B3C53] font-bold"
                            >
                                {{ $page.props.auth.user.name.charAt(0) }}
                            </div>
                            <span class="hidden sm:inline text-sm">
                                {{ $page.props.auth.user.name.split(' ')[0] }}
                            </span>
                            <svg class="w-4 h-4 ml-2" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a 1 1 0 01-1.414 0l-4-4a 1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            class="absolute right-0 mt-0 w-48 bg-white rounded-lg shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition duration-300 z-50"
                        >
                            <Link
                                href="/profile"
                                class="block px-4 py-3 text-gray-800 hover:bg-[#E3E3E3] first:rounded-t-lg text-sm font-medium"
                            >
                                👤 Profile
                            </Link>
                            <Link
                                href="/settings"
                                class="block px-4 py-3 text-gray-800 hover:bg-[#E3E3E3] text-sm font-medium"
                            >
                                ⚙️ Settings
                            </Link>
                            <form method="POST" :action="route('logout')" @submit.prevent="submitLogout">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token" />
                                <button
                                    type="submit"
                                    class="w-full text-left px-4 py-3 text-red-600 hover:bg-red-50 last:rounded-b-lg text-sm font-medium border-t"
                                >
                                    🚪 Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div v-if="showDropdown" class="md:hidden pb-4 space-y-2">
                <template v-for="item in navItems" :key="item.route">
                    <Link
                        :href="route(item.route)"
                        class="block px-4 py-2 rounded-lg text-white hover:bg-[#E3E3E3] hover:text-[#1B3C53] transition text-sm font-medium"
                        :class="{
                            'bg-[#E3E3E3] text-[#1B3C53]': currentPage.includes(
                                item.route.split('.')[0]
                            ),
                        }"
                    >
                        <span>{{ item.icon }}</span>
                        <span>{{ item.label }}</span>
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>

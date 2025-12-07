<script setup>
import Navbar from '@/Components/Navbar.vue';
</script>

<template>
    <div class="min-h-screen bg-[#E3E3E3]">
        <Navbar />
        <main>
            <slot />
        </main>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import Icon from '@/Components/Icon.vue';

const model = defineModel({ type: String, default: '' });
const visible = ref(false);
const input = ref(null);

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="relative">
        <input ref="input" v-model="model" :type="visible ? 'text' : 'password'" class="input pr-11" v-bind="$attrs" />
        <button
            type="button"
            class="absolute inset-y-0 right-0 grid w-11 place-items-center text-muted transition hover:text-ink"
            :aria-label="visible ? 'Hide password' : 'Show password'"
            @click="visible = !visible"
        >
            <Icon :name="visible ? 'eye-off' : 'eye'" class="h-[18px] w-[18px]" />
        </button>
    </div>
</template>

<script>
export default { inheritAttrs: false };
</script>

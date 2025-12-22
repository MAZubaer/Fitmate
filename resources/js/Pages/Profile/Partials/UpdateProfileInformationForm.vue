
<script setup>
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const user = computed(() => usePage().props.user);

const form = useForm({
	nickname: user.value.nickname || '',
	age: user.value.age || '',
	gender: user.value.gender || '',
});

const genders = [
	{ value: '', label: 'Select gender' },
	{ value: 'male', label: 'Male' },
	{ value: 'female', label: 'Female' },
	{ value: 'other', label: 'Other' },
	{ value: 'prefer_not_say', label: 'Prefer not to say' },
];

const submitting = ref(false);

function submit() {
	submitting.value = true;
	form.patch(route('profile.update'), {
		onFinish: () => { submitting.value = false; },
	});
}
</script>

<template>
	<form @submit.prevent="submit" class="space-y-6">
		<div>
			<label class="block text-sm font-medium text-gray-700">Name</label>
			<input type="text" :value="user.name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly />
		</div>
		<div>
			<label class="block text-sm font-medium text-gray-700">Email</label>
			<input type="email" :value="user.email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly />
		</div>
		<div>
			<label for="nickname" class="block text-sm font-medium text-gray-700">Nickname</label>
			<input id="nickname" v-model="form.nickname" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
			<div v-if="form.errors.nickname" class="text-red-500 text-xs mt-1">{{ form.errors.nickname }}</div>
		</div>
		<div>
			<label for="age" class="block text-sm font-medium text-gray-700">Age</label>
			<input id="age" v-model="form.age" type="number" min="0" max="120" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" />
			<div v-if="form.errors.age" class="text-red-500 text-xs mt-1">{{ form.errors.age }}</div>
		</div>
		<div>
			<label for="gender" class="block text-sm font-medium text-gray-700">Gender</label>
			<select id="gender" v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
				<option v-for="g in genders" :key="g.value" :value="g.value">{{ g.label }}</option>
			</select>
			<div v-if="form.errors.gender" class="text-red-500 text-xs mt-1">{{ form.errors.gender }}</div>
		</div>
		<div>
			<button type="submit" :disabled="submitting" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">
				Save
			</button>
			<span v-if="form.recentlySuccessful" class="ml-2 text-green-600">Saved!</span>
		</div>
	</form>
</template>

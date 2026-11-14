<script setup>
import { computed, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Icon from '@/Components/Icon.vue';
import Modal from '@/Components/Modal.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { toast } from '@/composables/useToast';
import { formatDuration, formatNumber, formatTime, parseKey, relativeDay, todayKey } from '@/utils/format';

const MUSCLE_GROUPS = ['Chest', 'Back', 'Shoulders', 'Arms', 'Legs', 'Abs/Core', 'Full Body'];

const groupTone = {
    Chest: { chip: 'bg-rose-500/10 text-rose-600 dark:text-rose-400', bar: 'bg-rose-500' },
    Back: { chip: 'bg-sky-500/10 text-sky-600 dark:text-sky-400', bar: 'bg-sky-500' },
    Shoulders: { chip: 'bg-amber-500/10 text-amber-600 dark:text-amber-400', bar: 'bg-amber-500' },
    Arms: { chip: 'bg-violet-500/10 text-violet-600 dark:text-violet-400', bar: 'bg-violet-500' },
    Legs: { chip: 'bg-brand-500/10 text-brand-600 dark:text-brand-400', bar: 'bg-brand-500' },
    'Abs/Core': { chip: 'bg-orange-500/10 text-orange-600 dark:text-orange-400', bar: 'bg-orange-500' },
    'Full Body': { chip: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400', bar: 'bg-cyan-500' },
};
const toneFor = (group) => groupTone[group] ?? { chip: 'bg-subtle text-muted', bar: 'bg-slate-400' };

const isDone = (w) => [true, 1, '1', 't', 'true'].includes(w.completed);

/* ---------------- Data ---------------- */
const workouts = ref([]);
const loading = ref(true);
const tab = ref('upcoming');
const search = ref('');

async function loadWorkouts() {
    try {
        const { data } = await axios.get('/workouts-data');
        workouts.value = data.map((w) => ({ ...w, completed: isDone(w) }));
    } catch {
        toast('Failed to load workouts', 'error');
    } finally {
        loading.value = false;
    }
}

onMounted(loadWorkouts);

const sortKey = (w) => `${String(w.date).slice(0, 10)} ${w.time ?? ''}`;
const upcoming = computed(() => workouts.value.filter((w) => !w.completed).sort((a, b) => sortKey(a).localeCompare(sortKey(b))));
const completed = computed(() => workouts.value.filter((w) => w.completed).sort((a, b) => sortKey(b).localeCompare(sortKey(a))));

const visible = computed(() => {
    const list = tab.value === 'upcoming' ? upcoming.value : completed.value;
    const q = search.value.trim().toLowerCase();
    if (!q) return list;
    return list.filter((w) => w.name.toLowerCase().includes(q) || (w.muscle_group ?? '').toLowerCase().includes(q));
});

const completionRate = computed(() => (workouts.value.length ? Math.round((completed.value.length / workouts.value.length) * 100) : 0));
const caloriesBurned = computed(() => completed.value.reduce((sum, w) => sum + Number(w.calories || 0), 0));
const timeTrained = computed(() => completed.value.reduce((sum, w) => sum + Number(w.duration || 0), 0));
const totalSets = computed(() => completed.value.reduce((sum, w) => sum + Number(w.sets || 0), 0));
const totalReps = computed(() => completed.value.reduce((sum, w) => sum + Number(w.sets || 0) * Number(w.reps || 0), 0));

const muscleBreakdown = computed(() => {
    const counts = {};
    completed.value.forEach((w) => {
        const group = w.muscle_group || 'Other';
        counts[group] = (counts[group] || 0) + 1;
    });
    const max = Math.max(1, ...Object.values(counts));
    return Object.entries(counts)
        .map(([group, count]) => ({ group, count, pct: (count / max) * 100 }))
        .sort((a, b) => b.count - a.count);
});

const isOverdue = (w) => {
    if (w.completed) return false;
    const date = parseKey(w.date);
    if (!date) return false;
    const [h, m] = String(w.time ?? '23:59').split(':').map(Number);
    date.setHours(h || 0, m || 0);
    return date < new Date();
};

/* ---------------- Create / edit ---------------- */
const showForm = ref(false);
const editing = ref(null); // workout being edited, or null
const saving = ref(false);
const errors = ref({});

// Next half-hour slot that's at least 30 minutes away.
const defaultTime = () => {
    const d = new Date();
    d.setMinutes(d.getMinutes() + 60 - (d.getMinutes() % 30 || 30));
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
};

const emptyForm = () => ({ name: '', sets: 3, reps: 10, calories: '', minutes: '', muscle_group: '', date: todayKey(), time: defaultTime() });
const form = ref(emptyForm());

function openCreate() {
    editing.value = null;
    form.value = emptyForm();
    errors.value = {};
    showForm.value = true;
}

function openEdit(w) {
    editing.value = w;
    form.value = {
        name: w.name,
        sets: w.sets,
        reps: w.reps,
        calories: w.calories,
        minutes: w.duration ? Math.round((w.duration / 60) * 10) / 10 : '',
        muscle_group: w.muscle_group ?? '',
        date: String(w.date).slice(0, 10),
        time: String(w.time ?? '').slice(0, 5),
    };
    errors.value = {};
    showForm.value = true;
}

const toPayload = (f, completedFlag = false) => ({
    name: f.name,
    sets: Number(f.sets),
    reps: Number(f.reps),
    calories: Number(f.calories || 0),
    duration: f.minutes === '' || f.minutes === null ? null : Math.round(Number(f.minutes) * 60),
    muscle_group: f.muscle_group || null,
    date: f.date,
    time: f.time,
    completed: completedFlag,
});

function validate(f) {
    const e = {};
    if (!f.name?.trim()) e.name = ['Give this exercise a name.'];
    if (!(Number(f.sets) >= 1)) e.sets = ['At least 1 set.'];
    if (!(Number(f.reps) >= 1)) e.reps = ['At least 1 rep.'];
    if (!f.date) e.date = ['Pick a date.'];
    if (!f.time) e.time = ['Pick a time.'];
    errors.value = e;
    return Object.keys(e).length === 0;
}

async function saveWorkout() {
    if (!validate(form.value)) return;
    saving.value = true;
    try {
        if (editing.value) {
            const { data } = await axios.put(`/workouts-data/${editing.value.id}`, toPayload(form.value, editing.value.completed));
            const i = workouts.value.findIndex((w) => w.id === editing.value.id);
            if (i !== -1) workouts.value[i] = { ...data, completed: isDone(data) };
            toast('Workout updated');
        } else {
            const { data } = await axios.post('/workouts-data', toPayload(form.value));
            workouts.value.unshift({ ...data, completed: false });
            tab.value = 'upcoming';
            toast('Workout scheduled');
        }
        showForm.value = false;
    } catch (e) {
        errors.value = e.response?.data?.errors ?? {};
        if (!e.response?.data?.errors) toast('Could not save the workout', 'error');
    } finally {
        saving.value = false;
    }
}

/* ---------------- Plan builder ---------------- */
const plan = ref([]);
const savingPlan = ref(false);

function addToPlan() {
    if (!validate(form.value)) return;
    plan.value.push({ key: Date.now() + Math.random(), ...form.value });
    toast(`Added "${form.value.name}" to your plan`, 'info');
    const { date, time } = form.value;
    form.value = { ...emptyForm(), date, time };
}

async function savePlan() {
    savingPlan.value = true;
    try {
        // Remove each item once saved so a retry after a failure never duplicates.
        while (plan.value.length) {
            const item = plan.value[0];
            const { data } = await axios.post('/workouts-data', toPayload(item));
            workouts.value.unshift({ ...data, completed: false });
            plan.value.shift();
        }
        tab.value = 'upcoming';
        toast('Plan saved to your schedule');
    } catch {
        toast(`Could not save the remaining ${plan.value.length} exercise(s)`, 'error');
    } finally {
        savingPlan.value = false;
    }
}

/* ---------------- Complete / delete ---------------- */
const completingId = ref(null);

async function completeWorkout(w) {
    completingId.value = w.id;
    try {
        const { data } = await axios.put(`/workouts-data/${w.id}/complete`);
        const i = workouts.value.findIndex((x) => x.id === w.id);
        if (i !== -1) workouts.value[i] = { ...workouts.value[i], ...data, completed: true };
        toast(`Nice! "${w.name}" completed`);
    } catch {
        toast('Could not mark the workout complete', 'error');
    } finally {
        completingId.value = null;
    }
}

const deleting = ref(null);
const deleteBusy = ref(false);

async function confirmDelete() {
    deleteBusy.value = true;
    try {
        await axios.delete(`/workouts-data/${deleting.value.id}`);
        workouts.value = workouts.value.filter((w) => w.id !== deleting.value.id);
        toast('Workout deleted');
        deleting.value = null;
    } catch {
        toast('Delete failed', 'error');
    } finally {
        deleteBusy.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Workouts" />

        <PageHeader title="Workouts" subtitle="Plan your sessions, check them off, and watch your progress add up.">
            <template #actions>
                <button class="btn-primary" @click="openCreate"><Icon name="plus" class="h-4 w-4" /> New workout</button>
            </template>
        </PageHeader>

        <!-- Stats -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 lg:gap-6">
            <StatCard label="Completion" :value="completionRate" unit="%" icon="trending-up" tone="brand" :progress="completionRate" :hint="`${completed.length} of ${workouts.length} sessions`" />
            <StatCard label="Calories burned" :value="formatNumber(caloriesBurned)" unit="kcal" icon="flame" tone="orange" hint="Across completed sessions" />
            <StatCard label="Time trained" :value="formatDuration(timeTrained)" icon="clock" tone="sky" hint="Total recorded duration" />
            <StatCard label="Volume" :value="formatNumber(totalReps)" unit="reps" icon="dumbbell" tone="violet" :hint="`${formatNumber(totalSets)} sets completed`" />
        </div>

        <!-- Plan builder -->
        <section v-if="plan.length" class="card mt-6 overflow-hidden border-brand-500/30">
            <div class="flex flex-col gap-3 border-b border-line bg-brand-500/5 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-500/15 text-brand-600 dark:text-brand-400"><Icon name="list-plus" class="h-[18px] w-[18px]" /></span>
                    <div>
                        <h2 class="card-title">Workout plan</h2>
                        <p class="text-sm text-muted">{{ plan.length }} exercise{{ plan.length === 1 ? '' : 's' }} ready to schedule</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button class="btn-ghost btn-sm" :disabled="savingPlan" @click="plan = []">Clear</button>
                    <button class="btn-secondary btn-sm" @click="openCreate"><Icon name="plus" class="h-3.5 w-3.5" /> Add more</button>
                    <button class="btn-primary btn-sm" :disabled="savingPlan" @click="savePlan">
                        <Icon name="check" class="h-3.5 w-3.5" /> {{ savingPlan ? 'Saving…' : `Save all (${plan.length})` }}
                    </button>
                </div>
            </div>
            <ul class="divide-y divide-line">
                <li v-for="(item, i) in plan" :key="item.key" class="flex items-center gap-3 px-5 py-3 sm:px-6">
                    <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-subtle text-xs font-bold text-muted">{{ i + 1 }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold">{{ item.name }}</p>
                        <p class="text-xs text-muted">{{ item.sets }} × {{ item.reps }} · {{ relativeDay(item.date) }} at {{ formatTime(item.time) }}</p>
                    </div>
                    <button class="btn-icon h-8 w-8" aria-label="Remove from plan" @click="plan.splice(i, 1)"><Icon name="x" class="h-4 w-4" /></button>
                </li>
            </ul>
        </section>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <!-- List -->
            <section class="card lg:col-span-2">
                <div class="flex flex-col gap-3 border-b border-line p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="segmented">
                        <button :aria-pressed="tab === 'upcoming'" @click="tab = 'upcoming'">
                            Upcoming <span class="ml-1 text-xs text-muted">{{ upcoming.length }}</span>
                        </button>
                        <button :aria-pressed="tab === 'completed'" @click="tab = 'completed'">
                            Completed <span class="ml-1 text-xs text-muted">{{ completed.length }}</span>
                        </button>
                    </div>
                    <div class="relative sm:w-64">
                        <Icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                        <input v-model="search" type="search" class="input pl-9" placeholder="Search exercises" aria-label="Search workouts" />
                    </div>
                </div>

                <div v-if="loading" class="divide-y divide-line">
                    <div v-for="i in 4" :key="i" class="flex items-center gap-4 p-4 sm:px-6">
                        <div class="skeleton h-11 w-11 rounded-xl" />
                        <div class="flex-1 space-y-2"><div class="skeleton h-4 w-1/3" /><div class="skeleton h-3 w-1/2" /></div>
                    </div>
                </div>

                <EmptyState
                    v-else-if="visible.length === 0 && search"
                    icon="search"
                    title="No matches"
                    :description="`Nothing matches “${search}”.`"
                />
                <EmptyState
                    v-else-if="visible.length === 0 && tab === 'upcoming'"
                    icon="calendar"
                    title="No upcoming workouts"
                    description="Schedule your next session and we'll remind you when it's time."
                >
                    <button class="btn-primary" @click="openCreate"><Icon name="plus" class="h-4 w-4" /> Schedule a workout</button>
                </EmptyState>
                <EmptyState
                    v-else-if="visible.length === 0"
                    icon="trophy"
                    title="No completed workouts yet"
                    description="Mark a workout as done and it will show up here."
                />

                <TransitionGroup v-else tag="ul" class="divide-y divide-line" move-class="transition duration-300" leave-active-class="transition duration-200" leave-to-class="opacity-0">
                    <li v-for="w in visible" :key="w.id" class="group flex gap-4 p-4 transition hover:bg-subtle/50 sm:px-6">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl" :class="w.completed ? 'bg-brand-500/10 text-brand-600 dark:text-brand-400' : toneFor(w.muscle_group).chip">
                            <Icon :name="w.completed ? 'check' : 'dumbbell'" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="truncate font-semibold">{{ w.name }}</p>
                                <span v-if="isOverdue(w)" class="badge-danger">Overdue</span>
                                <span v-if="w.muscle_group" class="badge" :class="toneFor(w.muscle_group).chip">{{ w.muscle_group }}</span>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted">
                                <span class="inline-flex items-center gap-1"><Icon name="calendar" class="h-3.5 w-3.5" /> {{ relativeDay(w.date) }} · {{ formatTime(w.time) }}</span>
                                <span class="inline-flex items-center gap-1"><Icon name="dumbbell" class="h-3.5 w-3.5" /> {{ w.sets }} × {{ w.reps }}</span>
                                <span class="inline-flex items-center gap-1"><Icon name="flame" class="h-3.5 w-3.5" /> {{ w.calories }} kcal</span>
                                <span v-if="w.duration" class="inline-flex items-center gap-1"><Icon name="clock" class="h-3.5 w-3.5" /> {{ formatDuration(w.duration) }}</span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-start gap-1">
                            <button
                                v-if="!w.completed"
                                class="btn-secondary btn-sm hidden sm:inline-flex"
                                :disabled="completingId === w.id"
                                @click="completeWorkout(w)"
                            >
                                <Icon name="check" class="h-3.5 w-3.5" /> Done
                            </button>
                            <button v-if="!w.completed" class="btn-icon h-8 w-8 text-brand-600 sm:hidden" aria-label="Mark complete" @click="completeWorkout(w)">
                                <Icon name="check-circle" class="h-[18px] w-[18px]" />
                            </button>
                            <button class="btn-icon h-8 w-8" aria-label="Edit workout" title="Edit" @click="openEdit(w)"><Icon name="pencil" class="h-4 w-4" /></button>
                            <button class="btn-icon h-8 w-8 hover:text-rose-600" aria-label="Delete workout" title="Delete" @click="deleting = w"><Icon name="trash" class="h-4 w-4" /></button>
                        </div>
                    </li>
                </TransitionGroup>
            </section>

            <!-- Muscle focus -->
            <section class="card card-pad h-fit">
                <h2 class="card-title">Muscle focus</h2>
                <p class="mt-0.5 text-sm text-muted">Completed sessions by muscle group</p>

                <div v-if="muscleBreakdown.length" class="mt-5 space-y-4">
                    <div v-for="m in muscleBreakdown" :key="m.group">
                        <div class="mb-1.5 flex items-center justify-between text-sm">
                            <span class="font-medium">{{ m.group }}</span>
                            <span class="tabular-nums text-muted">{{ m.count }} session{{ m.count === 1 ? '' : 's' }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-subtle">
                            <div class="h-full rounded-full transition-all duration-700" :class="toneFor(m.group).bar" :style="{ width: m.pct + '%' }" />
                        </div>
                    </div>
                </div>
                <div v-else class="mt-6 rounded-2xl border border-dashed border-line p-6 text-center text-sm text-muted">
                    Complete a workout to see which muscles you train most.
                </div>
            </section>
        </div>

        <!-- Create / edit modal -->
        <Modal
            :show="showForm"
            :title="editing ? 'Edit workout' : 'New workout'"
            :description="editing ? 'Update the details of this session.' : 'Schedule a session, or add several to a plan and save them together.'"
            max-width="lg"
            @close="showForm = false"
        >
            <form class="space-y-4" @submit.prevent="saveWorkout">
                <div>
                    <label class="label" for="w-name">Exercise</label>
                    <input id="w-name" v-model="form.name" class="input" placeholder="e.g. Bench press" autocomplete="off" />
                    <p v-if="errors.name" class="field-error">{{ errors.name[0] }}</p>
                </div>

                <div>
                    <span class="label">Muscle group</span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="g in MUSCLE_GROUPS"
                            :key="g"
                            type="button"
                            class="rounded-full border px-3 py-1.5 text-xs font-medium transition"
                            :class="form.muscle_group === g ? 'border-brand-500 bg-brand-500/10 text-brand-700 dark:text-brand-300' : 'border-line text-muted hover:border-muted/50 hover:text-ink'"
                            @click="form.muscle_group = form.muscle_group === g ? '' : g"
                        >
                            {{ g }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="label" for="w-sets">Sets</label>
                        <input id="w-sets" v-model="form.sets" type="number" min="1" inputmode="numeric" class="input" />
                        <p v-if="errors.sets" class="field-error">{{ errors.sets[0] }}</p>
                    </div>
                    <div>
                        <label class="label" for="w-reps">Reps</label>
                        <input id="w-reps" v-model="form.reps" type="number" min="1" inputmode="numeric" class="input" />
                        <p v-if="errors.reps" class="field-error">{{ errors.reps[0] }}</p>
                    </div>
                    <div>
                        <label class="label" for="w-cal">Calories</label>
                        <input id="w-cal" v-model="form.calories" type="number" min="0" inputmode="numeric" class="input" placeholder="kcal" />
                        <p v-if="errors.calories" class="field-error">{{ errors.calories[0] }}</p>
                    </div>
                    <div>
                        <label class="label" for="w-min">Duration</label>
                        <input id="w-min" v-model="form.minutes" type="number" min="0" step="0.5" inputmode="decimal" class="input" placeholder="min" />
                        <p v-if="errors.duration" class="field-error">{{ errors.duration[0] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="w-date">Date</label>
                        <input id="w-date" v-model="form.date" type="date" class="input" />
                        <p v-if="errors.date" class="field-error">{{ errors.date[0] }}</p>
                    </div>
                    <div>
                        <label class="label" for="w-time">Time</label>
                        <input id="w-time" v-model="form.time" type="time" class="input" />
                        <p v-if="errors.time" class="field-error">{{ errors.time[0] }}</p>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                    <button type="button" class="btn-ghost" @click="showForm = false">Cancel</button>
                    <button v-if="!editing" type="button" class="btn-secondary" @click="addToPlan">
                        <Icon name="list-plus" class="h-4 w-4" /> Add to plan
                    </button>
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Saving…' : editing ? 'Save changes' : 'Schedule workout' }}
                    </button>
                </div>
            </form>
        </Modal>

        <ConfirmDialog
            :show="!!deleting"
            title="Delete workout?"
            :message="deleting ? `“${deleting.name}” will be removed permanently.` : ''"
            :processing="deleteBusy"
            @confirm="confirmDelete"
            @close="deleting = null"
        />
    </AppLayout>
</template>

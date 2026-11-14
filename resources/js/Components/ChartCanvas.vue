<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Chart from 'chart.js/auto';
import { useTheme } from '@/composables/useTheme';

const props = defineProps({
    type: { type: String, default: 'bar' }, // 'bar' | 'line'
    labels: { type: Array, default: () => [] },
    // [{ label, data: number[], color: '#rrggbb' }]
    datasets: { type: Array, default: () => [] },
    unit: { type: String, default: '' },
    beginAtZero: { type: Boolean, default: true },
    emptyText: { type: String, default: 'No data yet' },
});

const canvas = ref(null);
const { isDark } = useTheme();
let chart = null;

const isEmpty = computed(() =>
    props.datasets.every((d) => d.data.every((v) => v === null || v === undefined || Number(v) === 0)),
);

const cssVar = (name, alpha = 1) => {
    const rgb = getComputedStyle(document.documentElement).getPropertyValue(`--${name}`).trim().split(/\s+/).join(', ');
    return `rgba(${rgb}, ${alpha})`;
};

const withAlpha = (hex, alpha) => {
    const n = parseInt(hex.replace('#', ''), 16);
    return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
};

const font = { family: '"Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif', size: 12 };

function toDataset(d) {
    if (props.type === 'line') {
        return {
            label: d.label,
            data: d.data,
            borderColor: d.color,
            backgroundColor: withAlpha(d.color, 0.12),
            fill: true,
            tension: 0.35,
            borderWidth: 2.5,
            pointRadius: 3.5,
            pointHoverRadius: 5,
            pointBackgroundColor: d.color,
            pointBorderColor: cssVar('surface'),
            pointBorderWidth: 2,
            spanGaps: true,
        };
    }
    return {
        label: d.label,
        data: d.data,
        backgroundColor: d.color,
        hoverBackgroundColor: withAlpha(d.color, 0.85),
        borderRadius: 6,
        borderSkipped: false,
        maxBarThickness: 26,
        categoryPercentage: 0.7,
        barPercentage: 0.85,
    };
}

function build() {
    chart?.destroy();
    chart = null;
    if (!canvas.value) return;

    const muted = cssVar('muted');
    const multi = props.datasets.length > 1;

    chart = new Chart(canvas.value, {
        type: props.type,
        data: { labels: props.labels, datasets: props.datasets.map(toDataset) },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            animation: { duration: 500 },
            plugins: {
                legend: {
                    display: multi,
                    position: 'top',
                    align: 'end',
                    labels: { color: muted, usePointStyle: true, pointStyle: 'circle', boxWidth: 6, boxHeight: 6, padding: 14, font },
                },
                tooltip: {
                    backgroundColor: isDark.value ? '#1c2029' : '#0f172a',
                    borderColor: isDark.value ? 'rgba(255,255,255,0.08)' : 'transparent',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 10,
                    titleFont: { ...font, weight: '600' },
                    bodyFont: font,
                    displayColors: multi,
                    usePointStyle: true,
                    callbacks: {
                        label: (ctx) =>
                            ctx.parsed.y === null
                                ? `${ctx.dataset.label}: —`
                                : `${ctx.dataset.label}: ${Number(ctx.parsed.y).toLocaleString('en-US')}${props.unit ? ' ' + props.unit : ''}`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: muted, font } },
                y: {
                    beginAtZero: props.beginAtZero,
                    grid: { color: cssVar('line', 0.8) },
                    border: { display: false },
                    ticks: { color: muted, font, maxTicksLimit: 5, padding: 6 },
                },
            },
        },
    });
}

onMounted(build);
onBeforeUnmount(() => chart?.destroy());
watch([() => props.labels, () => props.datasets, isDark], build, { deep: true });
</script>

<template>
    <div class="relative h-full w-full">
        <canvas ref="canvas" />
        <div v-if="isEmpty" class="pointer-events-none absolute inset-0 grid place-items-center">
            <span class="rounded-full border border-line bg-surface/90 px-3 py-1 text-xs font-medium text-muted backdrop-blur">
                {{ emptyText }}
            </span>
        </div>
    </div>
</template>

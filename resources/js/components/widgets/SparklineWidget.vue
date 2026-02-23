<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const formattedValue = computed(() => {
    const val = props.data.value
    const prefix = (props.data.meta?.prefix as string) ?? ''
    const suffix = (props.data.meta?.suffix as string) ?? ''

    let display: string
    if (typeof val === 'number') {
        display = val.toLocaleString()
    } else {
        display = String(val ?? '-')
    }

    return `${prefix}${display}${suffix}`
})

const sparkData = computed(() => {
    const series = props.data.series?.[0]?.data as number[] ?? []
    return series
})

const sparkColor = computed(() => {
    return (props.data.meta?.color as string) ?? '#3b82f6'
})

const status = computed(() => {
    return (props.data.meta?.status as string) ?? null
})

const statusColors = computed(() => {
    return (props.data.meta?.statusColors as Record<string, string>) ?? {}
})

const statusClass = computed(() => {
    return {
        normal: 'status-normal',
        warning: 'status-warning',
        critical: 'status-critical',
    }[status.value ?? 'normal'] ?? 'status-normal'
})

const statusStyle = computed(() => {
    const s = status.value
    if (!s || !statusColors.value[s]) return {}
    const c = statusColors.value[s]
    return { backgroundColor: `${c}1a`, color: c }
})

const sparkPath = computed(() => {
    const data = sparkData.value
    if (data.length < 2) return ''

    const max = Math.max(...data)
    const min = Math.min(...data)
    const range = max - min || 1
    const width = 200
    const height = 40
    const step = width / (data.length - 1)

    const points = data.map((v, i) => ({
        x: i * step,
        y: height - ((v - min) / range) * height,
    }))

    const line = points.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x},${p.y}`).join(' ')
    return line
})

const sparkAreaPath = computed(() => {
    if (!sparkPath.value) return ''
    const data = sparkData.value
    const width = 200
    const height = 40
    const step = width / (data.length - 1)
    return `${sparkPath.value} L${(data.length - 1) * step},${height} L0,${height} Z`
})

const changeClass = computed(() => {
    return {
        positive: 'change-positive',
        negative: 'change-negative',
        neutral: 'change-neutral',
    }[props.data.change_direction]
})

const changeIcon = computed(() => {
    return {
        positive: '\u2191',
        negative: '\u2193',
        neutral: '\u2192',
    }[props.data.change_direction]
})
</script>

<template>
    <div class="sparkline-widget">
        <div class="sparkline-top">
            <div class="sparkline-info">
                <div class="sparkline-value">{{ formattedValue }}</div>
                <div v-if="data.change_percent !== null" :class="['sparkline-change', changeClass]">
                    <span>{{ changeIcon }}</span>
                    <span>{{ Math.abs(data.change_percent) }}%</span>
                </div>
            </div>
            <span v-if="status" :class="['status-badge', statusClass]" :style="statusStyle">{{ status }}</span>
        </div>
        <svg v-if="sparkData.length >= 2" class="sparkline-svg" viewBox="0 0 200 40" preserveAspectRatio="none">
            <defs>
                <linearGradient :id="`spark-grad-${definition.key}`" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" :stop-color="sparkColor" stop-opacity="0.3" />
                    <stop offset="100%" :stop-color="sparkColor" stop-opacity="0.02" />
                </linearGradient>
            </defs>
            <path :d="sparkAreaPath" :fill="`url(#spark-grad-${definition.key})`" />
            <path :d="sparkPath" fill="none" :stroke="sparkColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </div>
</template>

<style scoped>
.sparkline-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
}

.sparkline-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
}

.sparkline-info { display: flex; flex-direction: column; gap: 0.125rem; }

.sparkline-value {
    font-size: clamp(1.25rem, 2vw, 2rem);
    font-weight: 700;
    color: var(--foreground, #0f172a);
    line-height: 1.2;
}

.sparkline-change {
    display: flex;
    align-items: center;
    gap: 0.125rem;
    font-size: 0.75rem;
}

.change-positive { color: var(--widget-positive); }
.change-negative { color: var(--widget-negative); }
.change-neutral { color: var(--widget-neutral); }

.status-badge {
    font-size: 0.625rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.125rem 0.375rem;
    border-radius: 999px;
}

.status-normal { background: var(--widget-positive-bg); color: var(--widget-positive-fg); }
.status-warning { background: var(--widget-warning-bg); color: var(--widget-warning-fg); }
.status-critical { background: var(--widget-critical-bg); color: var(--widget-critical-fg); }

.sparkline-svg {
    width: 100%;
    height: 40px;
    flex-shrink: 0;
    margin-top: auto;
}
</style>

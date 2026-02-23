<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const variant = computed(() => {
    return (props.data.meta?.variant as string) ?? 'default'
})

const subtitle = computed(() => {
    return (props.data.meta?.subtitle as string) ?? ''
})

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

const positiveColor = computed(() => (props.data.meta?.positiveColor as string) ?? null)
const negativeColor = computed(() => (props.data.meta?.negativeColor as string) ?? null)

const changeClass = computed(() => {
    return {
        positive: 'change-positive',
        negative: 'change-negative',
        neutral: 'change-neutral',
    }[props.data.change_direction]
})

const changeStyle = computed(() => {
    const dir = props.data.change_direction
    if (dir === 'positive' && positiveColor.value) return { color: positiveColor.value }
    if (dir === 'negative' && negativeColor.value) return { color: negativeColor.value }
    return {}
})

const badgeStyle = computed(() => {
    const dir = props.data.change_direction
    if (dir === 'positive' && positiveColor.value) return { color: positiveColor.value, backgroundColor: `${positiveColor.value}1a` }
    if (dir === 'negative' && negativeColor.value) return { color: negativeColor.value, backgroundColor: `${negativeColor.value}1a` }
    return {}
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
    <div :class="['stat-widget', `stat-variant-${variant}`]">
        <div class="stat-value">{{ formattedValue }}</div>

        <!-- Default variant -->
        <div v-if="variant === 'default' && data.change_percent !== null" :class="['stat-change', changeClass]" :style="changeStyle">
            <span class="change-icon">{{ changeIcon }}</span>
            <span>{{ Math.abs(data.change_percent) }}%</span>
            <span class="change-label">vs previous period</span>
        </div>

        <!-- Badge variant -->
        <div v-if="variant === 'badge' && data.change_percent !== null" class="stat-change-badge-row">
            <span :class="['stat-change-badge', changeClass]" :style="badgeStyle">
                {{ changeIcon }} {{ Math.abs(data.change_percent) }}%
            </span>
            <span class="change-label">vs previous period</span>
        </div>

        <!-- Revenue variant -->
        <div v-if="variant === 'revenue'" class="stat-revenue-row">
            <span v-if="data.change_percent !== null" :class="['stat-change-badge', changeClass]" :style="badgeStyle">
                {{ changeIcon }} {{ Math.abs(data.change_percent) }}%
            </span>
            <span v-if="subtitle" class="stat-subtitle">{{ subtitle }}</span>
        </div>
    </div>
</template>

<style scoped>
.stat-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
}

.stat-value {
    font-size: clamp(1.25rem, 2vw, 2rem);
    font-weight: 700;
    color: var(--foreground, #0f172a);
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.stat-variant-revenue .stat-value {
    font-size: clamp(1.5rem, 2.5vw, 2.5rem);
}

.stat-change {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-top: 0.25rem;
    font-size: 0.75rem;
}

.stat-change-badge-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.375rem;
}

.stat-change-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.125rem;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 0.0625rem 0.375rem;
    border-radius: 999px;
}

.stat-revenue-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.375rem;
}

.stat-subtitle {
    font-size: 0.75rem;
    color: var(--muted-foreground, #94a3b8);
}

.change-positive { color: var(--widget-positive); }
.change-negative { color: var(--widget-negative); }
.change-neutral { color: var(--widget-neutral); }

.stat-change-badge.change-positive { background: var(--widget-positive-bg); color: var(--widget-positive-fg); }
.stat-change-badge.change-negative { background: var(--widget-negative-bg); color: var(--widget-negative-fg); }
.stat-change-badge.change-neutral { background: var(--widget-neutral-bg); color: var(--widget-neutral-fg); }

.change-label {
    color: var(--muted-foreground, #94a3b8);
    margin-left: 0.25rem;
    font-size: 0.75rem;
}
</style>

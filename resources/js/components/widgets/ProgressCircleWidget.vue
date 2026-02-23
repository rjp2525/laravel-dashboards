<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const percentage = computed(() => {
    const val = Number(props.data.value)
    return Math.min(100, Math.max(0, isNaN(val) ? 0 : val))
})

const maxDisplay = computed(() => {
    return (props.data.meta?.max as number) ?? 100
})

const label = computed(() => {
    return (props.data.meta?.label as string) ?? ''
})

const color = computed(() => {
    return (props.data.meta?.color as string) ?? '#3b82f6'
})

const trackColor = computed(() => {
    return (props.data.meta?.trackColor as string) ?? null
})

const radius = 40
const circumference = 2 * Math.PI * radius

const strokeDashoffset = computed(() => {
    return circumference - (percentage.value / 100) * circumference
})
</script>

<template>
    <div class="progress-circle-widget">
        <div class="circle-container">
            <svg viewBox="0 0 100 100" class="circle-svg">
                <circle
                    cx="50" cy="50" :r="radius"
                    fill="none"
                    :stroke="trackColor ?? 'var(--muted, #f1f5f9)'"
                    stroke-width="8"
                />
                <circle
                    cx="50" cy="50" :r="radius"
                    fill="none"
                    :stroke="color"
                    stroke-width="8"
                    stroke-linecap="round"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="strokeDashoffset"
                    transform="rotate(-90 50 50)"
                    class="progress-ring"
                />
            </svg>
            <div class="circle-text">
                <span class="circle-value">{{ percentage }}%</span>
            </div>
        </div>
        <div class="circle-info">
            <div v-if="label" class="circle-label">{{ label }}</div>
            <div class="circle-fraction">
                <span class="fraction-current">{{ data.value }}</span>
                <span class="fraction-sep"> / </span>
                <span class="fraction-max">{{ maxDisplay }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.progress-circle-widget {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 1rem;
    overflow: hidden;
}

.circle-container {
    position: relative;
    width: 80px;
    height: 80px;
    flex-shrink: 0;
}

.circle-svg { width: 100%; height: 100%; }

.progress-ring { transition: stroke-dashoffset 0.6s ease; }

.circle-text {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.circle-value {
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--foreground, #0f172a);
}

.circle-info { display: flex; flex-direction: column; gap: 0.25rem; min-width: 0; }

.circle-label {
    font-size: 0.75rem;
    color: var(--muted-foreground, #64748b);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 500;
}

.circle-fraction {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--foreground, #0f172a);
}

.fraction-sep, .fraction-max {
    color: var(--muted-foreground, #94a3b8);
    font-weight: 400;
}
</style>

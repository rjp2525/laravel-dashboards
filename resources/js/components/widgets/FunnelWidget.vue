<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const steps = computed(() => {
    return (props.data.rows ?? []) as { name: string; value: number }[]
})

const maxValue = computed(() => {
    return Math.max(...steps.value.map(s => s.value), 1)
})

const stepsWithConversion = computed(() => {
    return steps.value.map((step, i) => {
        const prev = i > 0 ? steps.value[i - 1].value : null
        const conversionRate = prev !== null && prev > 0 ? (step.value / prev) * 100 : 100
        const widthPercent = (step.value / maxValue.value) * 100
        return { ...step, conversionRate: Math.round(conversionRate * 10) / 10, widthPercent }
    })
})

const defaultColors = [
    '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#c084fc', '#d8b4fe', '#e9d5ff',
]

const metaColors = computed(() => {
    return (props.data.meta?.colors as string[]) ?? []
})

function getColor(index: number): string {
    const row = steps.value[index] as any
    return row?.color ?? metaColors.value[index] ?? defaultColors[index % defaultColors.length]
}
</script>

<template>
    <div class="funnel-widget">
        <div v-for="(step, i) in stepsWithConversion" :key="i" class="funnel-step">
            <div class="funnel-bar-container">
                <div
                    class="funnel-bar"
                    :style="{ width: step.widthPercent + '%', backgroundColor: getColor(i) }"
                ></div>
            </div>
            <div class="funnel-info">
                <span class="funnel-name">{{ step.name }}</span>
                <span class="funnel-stats">
                    <span class="funnel-value">{{ step.value.toLocaleString() }}</span>
                    <span v-if="i > 0" class="funnel-rate">({{ step.conversionRate }}%)</span>
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.funnel-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    overflow: hidden;
}

.funnel-step {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.funnel-bar-container {
    width: 100%;
    display: flex;
    justify-content: center;
}

.funnel-bar {
    height: 1.75rem;
    border-radius: 0.25rem;
    transition: width 0.4s ease;
    min-width: 2rem;
}

.funnel-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 0.25rem;
}

.funnel-name {
    font-size: 0.75rem;
    color: var(--muted-foreground, #64748b);
}

.funnel-stats { display: flex; align-items: center; gap: 0.25rem; }

.funnel-value {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--foreground, #0f172a);
}

.funnel-rate {
    font-size: 0.6875rem;
    color: var(--muted-foreground, #94a3b8);
}
</style>

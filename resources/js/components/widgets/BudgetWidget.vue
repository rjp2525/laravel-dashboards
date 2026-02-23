<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const spent = computed(() => {
    const val = Number(props.data.value)
    return isNaN(val) ? 0 : val
})

const budget = computed(() => {
    return (props.data.meta?.budget as number) ?? 100
})

const budgetLabel = computed(() => {
    return (props.data.meta?.budgetLabel as string) ?? ''
})

const color = computed(() => {
    return (props.data.meta?.color as string) ?? '#3b82f6'
})

const prefix = computed(() => {
    return (props.data.meta?.prefix as string) ?? ''
})

const suffix = computed(() => {
    return (props.data.meta?.suffix as string) ?? ''
})

const percentage = computed(() => {
    if (budget.value === 0) return 0
    return Math.min(100, Math.max(0, (spent.value / budget.value) * 100))
})

const formattedSpent = computed(() => {
    return `${prefix.value}${spent.value.toLocaleString()}${suffix.value}`
})

const formattedBudget = computed(() => {
    return `${prefix.value}${budget.value.toLocaleString()}${suffix.value}`
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
    <div class="budget-widget">
        <div class="budget-header">
            <div class="budget-label-row">
                <span class="budget-dot" :style="{ backgroundColor: color }"></span>
                <span class="budget-title">{{ definition.label }}</span>
                <span v-if="data.change_percent !== null" :class="['budget-change', changeClass]">
                    {{ changeIcon }} {{ Math.abs(data.change_percent) }}%
                </span>
            </div>
        </div>
        <div class="budget-values">
            <span class="budget-spent">{{ formattedSpent }}</span>
            <span v-if="budgetLabel" class="budget-sublabel">{{ budgetLabel }}</span>
            <span v-else class="budget-sublabel">of {{ formattedBudget }}</span>
        </div>
        <div class="budget-bar-bg">
            <div class="budget-bar-fill" :style="{ width: percentage + '%', backgroundColor: color }"></div>
        </div>
    </div>
</template>

<style scoped>
.budget-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.375rem;
    overflow: hidden;
}

.budget-header { display: flex; flex-direction: column; gap: 0.125rem; }

.budget-label-row {
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.budget-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.budget-title {
    font-size: 0.8125rem;
    color: var(--muted-foreground, #64748b);
    font-weight: 500;
}

.budget-change {
    font-size: 0.6875rem;
    font-weight: 500;
    margin-left: auto;
    padding: 0.0625rem 0.375rem;
    border-radius: 999px;
}

.change-positive { background: var(--widget-positive-bg); color: var(--widget-positive-fg); }
.change-negative { background: var(--widget-negative-bg); color: var(--widget-negative-fg); }
.change-neutral { background: var(--widget-neutral-bg); color: var(--widget-neutral-fg); }

.budget-values { display: flex; align-items: baseline; gap: 0.375rem; }

.budget-spent {
    font-size: clamp(1.125rem, 1.5vw, 1.5rem);
    font-weight: 700;
    color: var(--foreground, #0f172a);
    line-height: 1.2;
}

.budget-sublabel {
    font-size: 0.75rem;
    color: var(--muted-foreground, #94a3b8);
}

.budget-bar-bg {
    width: 100%;
    height: 0.375rem;
    background: var(--muted, #f1f5f9);
    border-radius: 999px;
    overflow: hidden;
}

.budget-bar-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}
</style>

<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const totalValue = computed(() => {
    const val = props.data.value
    if (typeof val === 'number') return val
    return Number(val) || 0
})

const rows = computed(() => {
    return (props.data.rows ?? []) as { name: string; value: number; color: string }[]
})

const totalRowValues = computed(() => {
    return rows.value.reduce((sum, r) => sum + r.value, 0) || 1
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
    <div class="category-widget">
        <div class="category-header">
            <span class="category-value">{{ totalValue }}%</span>
            <span v-if="data.change_percent !== null" :class="['category-change', changeClass]">
                {{ changeIcon }} {{ Math.abs(data.change_percent) }}%
            </span>
        </div>
        <div class="category-bar">
            <div
                v-for="(row, i) in rows"
                :key="i"
                class="category-segment"
                :style="{ width: (row.value / totalRowValues) * 100 + '%', backgroundColor: row.color }"
            ></div>
        </div>
        <div class="category-legend">
            <div v-for="(row, i) in rows" :key="i" class="legend-item">
                <span class="legend-dot" :style="{ backgroundColor: row.color }"></span>
                <span class="legend-name">{{ row.name }}</span>
                <span class="legend-value">{{ row.value }}%</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.category-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    overflow: hidden;
}

.category-header {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.category-value {
    font-size: clamp(1.25rem, 2vw, 2rem);
    font-weight: 700;
    color: var(--foreground, #0f172a);
    line-height: 1.2;
}

.category-change {
    font-size: 0.75rem;
    font-weight: 500;
}

.change-positive { color: var(--widget-positive); }
.change-negative { color: var(--widget-negative); }
.change-neutral { color: var(--widget-neutral); }

.category-bar {
    display: flex;
    height: 0.5rem;
    border-radius: 999px;
    overflow: hidden;
    gap: 2px;
}

.category-segment {
    height: 100%;
    border-radius: 999px;
    transition: width 0.4s ease;
    min-width: 4px;
}

.category-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
}

.legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.legend-name { color: var(--muted-foreground, #64748b); }
.legend-value { font-weight: 600; color: var(--foreground, #0f172a); }
</style>

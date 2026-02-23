<script setup lang="ts">
import { computed, ref } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const showAll = ref(false)
const defaultLimit = 5

const rows = computed(() => {
    return (props.data.rows ?? []) as { name: string; value: number; href?: string; color?: string }[]
})

const maxValue = computed(() => {
    return Math.max(...rows.value.map(r => r.value), 1)
})

const visibleRows = computed(() => {
    if (showAll.value) return rows.value
    return rows.value.slice(0, defaultLimit)
})

const defaultColor = computed(() => {
    return (props.data.meta?.color as string) ?? '#3b82f6'
})

const valuePrefix = computed(() => {
    return (props.data.meta?.valuePrefix as string) ?? ''
})

const valueSuffix = computed(() => {
    return (props.data.meta?.valueSuffix as string) ?? ''
})

function formatValue(val: number): string {
    return `${valuePrefix.value}${val.toLocaleString()}${valueSuffix.value}`
}
</script>

<template>
    <div class="bar-list-widget">
        <div class="bar-list-rows">
            <div v-for="(row, i) in visibleRows" :key="i" class="bar-list-item">
                <div class="bar-list-bar-bg">
                    <div
                        class="bar-list-bar-fill"
                        :style="{ width: (row.value / maxValue) * 100 + '%', backgroundColor: row.color ?? defaultColor }"
                    ></div>
                    <component :is="row.href ? 'a' : 'span'" :href="row.href" class="bar-list-name">
                        {{ row.name }}
                    </component>
                </div>
                <span class="bar-list-value">{{ formatValue(row.value) }}</span>
            </div>
        </div>
        <button
            v-if="rows.length > defaultLimit"
            class="bar-list-toggle"
            @click="showAll = !showAll"
        >
            {{ showAll ? 'Show less' : `Show ${rows.length - defaultLimit} more` }}
        </button>
    </div>
</template>

<style scoped>
.bar-list-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    overflow: hidden;
}

.bar-list-rows { display: flex; flex-direction: column; gap: 0.375rem; }

.bar-list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.bar-list-bar-bg {
    flex: 1;
    position: relative;
    height: 1.75rem;
    background: var(--muted, #f1f5f9);
    border-radius: 0.25rem;
    overflow: hidden;
    min-width: 0;
}

.bar-list-bar-fill {
    position: absolute;
    inset: 0;
    border-radius: 0.25rem;
    opacity: 0.15;
    transition: width 0.4s ease;
}

.bar-list-name {
    position: relative;
    z-index: 1;
    display: block;
    padding: 0 0.5rem;
    line-height: 1.75rem;
    font-size: 0.8125rem;
    color: var(--foreground, #0f172a);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-decoration: none;
}

a.bar-list-name:hover { text-decoration: underline; }

.bar-list-value {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--foreground, #0f172a);
    white-space: nowrap;
    min-width: 3rem;
    text-align: right;
}

.bar-list-toggle {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--muted-foreground, #64748b);
    padding: 0.25rem 0;
    text-align: left;
}
.bar-list-toggle:hover { color: var(--foreground, #0f172a); }
</style>

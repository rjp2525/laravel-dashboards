<script setup lang="ts">
import { computed, ref } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const percentage = computed(() => {
    const val = Number(props.data.value)
    return Math.min(100, Math.max(0, isNaN(val) ? 0 : val))
})

const el = ref<HTMLElement | null>(null)

const barColor = computed(() => {
    const metaColor = props.data.meta?.color as string | undefined
    if (metaColor) return metaColor

    // Read CSS custom properties for threshold colors at runtime
    const styles = el.value ? getComputedStyle(el.value) : null
    const positive = styles?.getPropertyValue('--widget-positive')?.trim() || '#16a34a'
    const warning = styles?.getPropertyValue('--widget-warning')?.trim() || '#eab308'
    const negative = styles?.getPropertyValue('--widget-negative')?.trim() || '#dc2626'

    if (percentage.value >= 80) return positive
    if (percentage.value >= 50) return warning
    return negative
})

const current = computed(() => {
    return (props.data.meta?.current as number) ?? null
})

const total = computed(() => {
    return (props.data.meta?.total as number) ?? null
})

const href = computed(() => {
    return (props.data.meta?.href as string) ?? null
})
</script>

<template>
    <div ref="el" class="progress-widget">
        <div class="progress-top">
            <div class="progress-value">{{ percentage }}%</div>
            <div v-if="current !== null && total !== null" class="progress-count">
                {{ current.toLocaleString() }} of {{ total.toLocaleString() }}
            </div>
        </div>
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" :style="{ width: percentage + '%', backgroundColor: barColor }"></div>
        </div>
        <a v-if="href" :href="href" class="progress-link">View more &rarr;</a>
    </div>
</template>

<style scoped>
.progress-widget { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 0.5rem; }

.progress-top { display: flex; align-items: baseline; justify-content: space-between; }

.progress-value { font-size: 1.5rem; font-weight: 700; color: var(--foreground, #0f172a); }

.progress-count { font-size: 0.75rem; color: var(--muted-foreground, #64748b); }

.progress-bar-bg { width: 100%; height: 0.5rem; background: var(--muted, #f1f5f9); border-radius: 999px; overflow: hidden; }
.progress-bar-fill { height: 100%; border-radius: 999px; transition: width 0.5s ease; }

.progress-link {
    font-size: 0.75rem;
    color: var(--muted-foreground, #64748b);
    text-decoration: none;
}
.progress-link:hover { color: var(--foreground, #0f172a); text-decoration: underline; }
</style>

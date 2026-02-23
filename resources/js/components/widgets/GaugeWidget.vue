<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const rows = computed(() => {
    return (props.data.rows ?? []) as { name: string; value: number; color?: string }[]
})

const centerLabel = computed(() => {
    return (props.data.meta?.centerLabel as string) ?? ''
})

const centerValue = computed(() => {
    return (props.data.meta?.centerValue as string | number) ?? ''
})

const defaultColors = ['#3b82f6', '#f59e0b', '#e2e8f0', '#ef4444', '#10b981', '#8b5cf6', '#ec4899']

const metaColors = computed(() => {
    return (props.data.meta?.colors as string[]) ?? []
})

function getColor(index: number): string {
    return rows.value[index]?.color ?? metaColors.value[index] ?? defaultColors[index % defaultColors.length]
}

const totalValue = computed(() => {
    return rows.value.reduce((sum, r) => sum + r.value, 0) || 1
})

const outerR = 90
const innerR = 60
const cx = 100
const cy = 100

// angleDeg: 180 = left, 90 = top, 0 = right
function pt(angleDeg: number, r: number): { x: number; y: number } {
    const rad = (angleDeg * Math.PI) / 180
    return {
        x: cx + r * Math.cos(rad),
        y: cy - r * Math.sin(rad),
    }
}

const segments = computed(() => {
    const result: { path: string; color: string }[] = []
    const gapDeg = 1.5
    let currentAngle = 180

    for (let i = 0; i < rows.value.length; i++) {
        const fraction = rows.value[i].value / totalValue.value
        const sweepDeg = fraction * 180

        const startAngle = currentAngle - (i === 0 ? 0 : gapDeg / 2)
        const endAngle = currentAngle - sweepDeg + (i === rows.value.length - 1 ? 0 : gapDeg / 2)
        const actualSweep = startAngle - endAngle
        const largeArc = actualSweep > 180 ? 1 : 0

        const o1 = pt(startAngle, outerR)
        const o2 = pt(endAngle, outerR)
        const i1 = pt(endAngle, innerR)
        const i2 = pt(startAngle, innerR)

        // Outer arc: sweep-flag 1 (CW in SVG screen coords = left-to-right across top)
        // Inner arc: sweep-flag 0 (CCW in SVG screen coords = right-to-left back)
        const path = [
            `M ${o1.x.toFixed(2)} ${o1.y.toFixed(2)}`,
            `A ${outerR} ${outerR} 0 ${largeArc} 1 ${o2.x.toFixed(2)} ${o2.y.toFixed(2)}`,
            `L ${i1.x.toFixed(2)} ${i1.y.toFixed(2)}`,
            `A ${innerR} ${innerR} 0 ${largeArc} 0 ${i2.x.toFixed(2)} ${i2.y.toFixed(2)}`,
            'Z',
        ].join(' ')

        result.push({ path, color: getColor(i) })
        currentAngle -= sweepDeg
    }

    return result
})
</script>

<template>
    <div class="gauge-widget">
        <div class="gauge-svg-container">
            <svg viewBox="0 0 200 115" class="gauge-svg">
                <path
                    v-for="(seg, i) in segments"
                    :key="i"
                    :d="seg.path"
                    :fill="seg.color"
                />
            </svg>
            <div class="gauge-center">
                <div v-if="centerValue !== ''" class="gauge-center-value">{{ centerValue }}</div>
                <div v-if="centerLabel" class="gauge-center-label">{{ centerLabel }}</div>
            </div>
        </div>
        <div v-if="rows.length" class="gauge-legend">
            <div v-for="(row, i) in rows" :key="i" class="gauge-legend-item">
                <span class="gauge-legend-dot" :style="{ backgroundColor: getColor(i) }"></span>
                <span class="gauge-legend-name">{{ row.name }}</span>
                <span class="gauge-legend-value">{{ row.value }}</span>
            </div>
        </div>
    </div>
</template>

<style scoped>
.gauge-widget {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    overflow: hidden;
}

.gauge-svg-container {
    position: relative;
    width: 100%;
    max-width: 200px;
}

.gauge-svg { width: 100%; height: auto; }

.gauge-center {
    position: absolute;
    bottom: 8px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
}

.gauge-center-value {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--foreground, #0f172a);
    line-height: 1.2;
}

.gauge-center-label {
    font-size: 0.6875rem;
    color: var(--muted-foreground, #64748b);
}

.gauge-legend {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.5rem 1rem;
    width: 100%;
}

.gauge-legend-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.75rem;
}

.gauge-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.gauge-legend-name { color: var(--muted-foreground, #64748b); }
.gauge-legend-value { font-weight: 600; color: var(--foreground, #0f172a); }
</style>

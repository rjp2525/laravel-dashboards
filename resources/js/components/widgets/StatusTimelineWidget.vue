<script setup lang="ts">
import type { WidgetData, WidgetDefinition } from '@/types/widget'

import { computed, ref } from 'vue'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const el = ref<HTMLElement | null>(null)

const metaStatusColors = computed(() => {
    return (props.data.meta?.statusColors as Record<string, string>) ?? {}
})

function statusColor(status: string): string {
    // Per-widget meta override
    if (metaStatusColors.value[status]) return metaStatusColors.value[status]

    // CSS custom property fallbacks
    const styles = el.value ? getComputedStyle(el.value) : null
    switch (status) {
        case 'operational': return styles?.getPropertyValue('--widget-positive')?.trim() || '#16a34a'
        case 'degraded': return styles?.getPropertyValue('--widget-warning')?.trim() || '#eab308'
        case 'down':
        case 'outage': return styles?.getPropertyValue('--widget-negative')?.trim() || '#dc2626'
        default: return 'var(--muted-foreground, #94a3b8)'
    }
}
</script>

<template>
    <div ref="el" class="timeline-widget">
        <div v-for="(service, idx) in data.rows" :key="idx" class="timeline-row">
            <span class="service-name">{{ (service as any).name }}</span>
            <div class="timeline-bar">
                <div
                    v-for="(entry, sIdx) in (service as any).entries"
                    :key="sIdx"
                    class="timeline-segment"
                    :style="{ backgroundColor: statusColor(entry.status) }"
                    :title="`${entry.date}: ${entry.status}`"
                />
            </div>
            <span class="service-uptime">{{ (service as any).uptime }}%</span>
        </div>
        <p v-if="!data.rows?.length" class="timeline-empty">No services</p>
    </div>
</template>

<style scoped>
.timeline-widget { flex: 1; display: flex; flex-direction: column; gap: 0.375rem; overflow-y: auto; }
.timeline-row { display: flex; align-items: center; gap: 0.75rem; }
.service-name { font-size: 0.75rem; font-weight: 500; color: var(--foreground, inherit); white-space: nowrap; min-width: 6rem; }
.service-uptime { font-size: 0.625rem; color: var(--muted-foreground, #64748b); white-space: nowrap; min-width: 3rem; text-align: right; }
.timeline-bar { display: flex; flex: 1; gap: 2px; height: 0.5rem; align-items: center; }
.timeline-segment { flex: 1; border-radius: 1px; min-width: 2px; height: 100%; }
.timeline-empty { text-align: center; color: var(--muted-foreground, #94a3b8); font-size: 0.875rem; padding: 1rem; }
</style>

<script setup lang="ts">
import { computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'
import { useChartRenderer } from '@/composables/useChartRenderer'
import { useDarkMode } from '@/composables/useDarkMode'

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const { isDark } = useDarkMode()
const { adapter, component: ChartComponent } = useChartRenderer()

const chartOptions = computed(() => {
    return adapter.buildOptions(
        {
            type: props.definition.type as any,
            series: props.data.series,
            labels: props.data.labels,
            options: props.definition.chart_options,
        },
        isDark.value,
    )
})
</script>

<template>
    <div class="chart-widget">
        <component :is="ChartComponent" :options="chartOptions" class="chart" />
    </div>
</template>

<style scoped>
.chart-widget {
    flex: 1;
    min-height: 0;
}

.chart {
    width: 100%;
    height: 100%;
}
</style>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'
import { useChartRenderer } from '@/composables/useChartRenderer'

const VChart = defineAsyncComponent(() => import('vue-echarts'))

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const { name: rendererName } = useChartRenderer()
const isECharts = computed(() => rendererName === 'echarts')

const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const chartOptions = computed(() => {
    const rows = props.data.rows ?? []
    const weeks: string[] = []
    const heatmapData: [number, number, number][] = []
    let maxValue = 0

    rows.forEach((row: any) => {
        const d = new Date(row.date)
        const weekLabel = `W${Math.ceil((d.getDate()) / 7)}`
        let weekIdx = weeks.indexOf(weekLabel)
        if (weekIdx === -1) {
            weeks.push(weekLabel)
            weekIdx = weeks.length - 1
        }
        const dayIdx = d.getDay()
        const val = row.value ?? 0
        if (val > maxValue) maxValue = val
        heatmapData.push([weekIdx, dayIdx, val])
    })

    return {
        tooltip: {
            position: 'top',
            formatter: (params: any) => `${params.value[2]} contributions`,
        },
        grid: {
            top: 10,
            bottom: 10,
            left: 40,
            right: 10,
        },
        xAxis: {
            type: 'category',
            data: weeks,
            splitArea: { show: true },
            axisLabel: { show: false },
            axisTick: { show: false },
        },
        yAxis: {
            type: 'category',
            data: dayNames,
            splitArea: { show: true },
            axisLabel: { color: 'var(--muted-foreground, #64748b)', fontSize: 10 },
        },
        visualMap: {
            min: 0,
            max: maxValue || 50,
            show: false,
            inRange: {
                color: (props.data.meta?.colors as string[]) ?? ['#ebedf0', '#9be9a8', '#40c463', '#30a14e', '#216e39'],
            },
        },
        series: [
            {
                type: 'heatmap',
                data: heatmapData,
                label: { show: false },
                itemStyle: { borderRadius: 2, borderColor: 'var(--card, white)', borderWidth: 2 },
            },
        ],
    }
})
</script>

<template>
    <div class="heatmap-widget">
        <template v-if="isECharts">
            <VChart :option="chartOptions" autoresize class="chart" />
        </template>
        <template v-else>
            <div class="heatmap-fallback">
                <p>Heatmap widget requires ECharts renderer.</p>
                <p>Install with: <code>app.use(LaravelDashboard, { renderer: EChartsRenderer })</code></p>
            </div>
        </template>
    </div>
</template>

<style scoped>
.heatmap-widget {
    flex: 1;
    min-height: 0;
}
.chart {
    width: 100%;
    height: 100%;
}
.heatmap-fallback {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--muted-foreground, #64748b);
    font-size: 0.875rem;
    text-align: center;
    gap: 0.25rem;
}
.heatmap-fallback code {
    font-size: 0.75rem;
    background: var(--muted, #f1f5f9);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}
</style>

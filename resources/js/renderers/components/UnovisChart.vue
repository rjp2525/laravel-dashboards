<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps<{
    options: Record<string, unknown>
}>()

const componentMap: Record<string, ReturnType<typeof defineAsyncComponent>> = {
    line: defineAsyncComponent(() => import('vue-chrts').then((m) => m.LineChart)),
    bar: defineAsyncComponent(() => import('vue-chrts').then((m) => m.BarChart)),
    area: defineAsyncComponent(() => import('vue-chrts').then((m) => m.AreaChart)),
    donut: defineAsyncComponent(() => import('vue-chrts').then((m) => m.DonutChart)),
}

const chartType = computed(() => (props.options.chartType as string) ?? 'line')
const chartComponent = computed(() => componentMap[chartType.value] ?? componentMap.line)
const isBar = computed(() => chartType.value === 'bar')
</script>

<template>
    <div class="unovis-chart">
        <component
            :is="chartComponent"
            :data="options.data"
            :categories="options.categories"
            :height="(options.height as number) ?? 250"
            v-bind="isBar ? { yAxis: options.yAxis, xAxis: options.xAxis } : {}"
        />
    </div>
</template>

<style scoped>
.unovis-chart {
    width: 100%;
    height: 100%;
}
</style>

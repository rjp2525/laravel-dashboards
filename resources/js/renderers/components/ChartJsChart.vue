<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps<{
    options: Record<string, unknown>
}>()

const componentMap: Record<string, ReturnType<typeof defineAsyncComponent>> = {
    line: defineAsyncComponent(() => import('vue-chartjs').then((m) => m.Line)),
    bar: defineAsyncComponent(() => import('vue-chartjs').then((m) => m.Bar)),
    pie: defineAsyncComponent(() => import('vue-chartjs').then((m) => m.Pie)),
    doughnut: defineAsyncComponent(() => import('vue-chartjs').then((m) => m.Doughnut)),
}

const chartComponent = computed(() => {
    const type = (props.options.chartType as string) ?? 'line'
    return componentMap[type] ?? componentMap.line
})
</script>

<template>
    <div class="chartjs-chart">
        <component
            :is="chartComponent"
            :data="(options.data as Record<string, unknown>)"
            :options="(options.options as Record<string, unknown>)"
        />
    </div>
</template>

<style scoped>
.chartjs-chart {
    width: 100%;
    height: 100%;
    position: relative;
}
</style>

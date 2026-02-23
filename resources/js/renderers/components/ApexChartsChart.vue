<script setup lang="ts">
const props = defineProps<{
    options: Record<string, unknown>
}>()
</script>

<template>
    <apexchart
        :type="(options.chartType as string)"
        :options="(options.chartOptions as Record<string, unknown>)"
        :series="(options.series as unknown[])"
        height="100%"
        class="apexcharts-chart"
    />
</template>

<style scoped>
.apexcharts-chart {
    width: 100%;
    height: 100%;
}
</style>

<script setup lang="ts">
import VChart from 'vue-echarts'

defineProps<{
    options: Record<string, unknown>
}>()
</script>

<template>
    <VChart :option="options" autoresize class="echarts-chart" />
</template>

<style scoped>
.echarts-chart {
    width: 100%;
    height: 100%;
}
</style>

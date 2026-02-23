<script setup lang="ts">
import { usePeriod } from '@/composables/usePeriod'

const { activePeriod, periods, setPeriod } = usePeriod()
</script>

<template>
    <select
        class="period-selector"
        :value="activePeriod"
        @change="setPeriod(($event.target as HTMLSelectElement).value)"
    >
        <option v-for="period in periods" :key="period.value" :value="period.value">
            {{ period.label }}
        </option>
    </select>
</template>

<style scoped>
.period-selector {
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 0.375rem;
    background: var(--card, white);
    color: var(--foreground, inherit);
    font-size: 0.875rem;
    cursor: pointer;
}
</style>

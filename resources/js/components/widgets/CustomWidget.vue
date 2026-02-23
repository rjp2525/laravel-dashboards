<script setup lang="ts">
import type { WidgetData, WidgetDefinition } from '@/types/widget'

defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()
</script>

<template>
    <div class="custom-widget">
        <slot :data="data" :definition="definition">
            <p class="custom-placeholder">Custom widget: {{ definition.component }}</p>
        </slot>
    </div>
</template>

<style scoped>
.custom-widget { flex: 1; }
.custom-placeholder { color: #94a3b8; font-size: 0.875rem; text-align: center; padding: 1rem; }
</style>

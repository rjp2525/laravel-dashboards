<script setup lang="ts">
import type { WidgetData, WidgetDefinition } from '@/types/widget'

defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()
</script>

<template>
    <div class="list-widget">
        <div v-for="(row, idx) in data.rows" :key="idx" class="list-item">
            <div class="list-item-content">
                <span class="list-item-title">{{ row.title ?? row.name ?? Object.values(row)[0] }}</span>
                <span v-if="row.subtitle ?? row.description" class="list-item-subtitle">
                    {{ row.subtitle ?? row.description }}
                </span>
            </div>
            <span v-if="row.value !== undefined" class="list-item-value">{{ row.value }}</span>
        </div>
        <p v-if="data.rows.length === 0" class="list-empty">No items</p>
    </div>
</template>

<style scoped>
.list-widget { flex: 1; overflow-y: auto; }
.list-item { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--border, #f1f5f9); }
.list-item:last-child { border-bottom: none; }
.list-item-content { display: flex; flex-direction: column; }
.list-item-title { font-size: 0.875rem; font-weight: 500; color: var(--foreground, inherit); }
.list-item-subtitle { font-size: 0.75rem; color: var(--muted-foreground, #94a3b8); }
.list-item-value { font-size: 0.875rem; font-weight: 600; color: var(--foreground, #0f172a); }
.list-empty { text-align: center; color: var(--muted-foreground, #94a3b8); font-size: 0.875rem; padding: 1rem; }
</style>

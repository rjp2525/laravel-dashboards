<script setup lang="ts">
import { ref, computed } from 'vue'
import type { WidgetDefinition } from '@/types/widget'

const props = defineProps<{
    widgets: WidgetDefinition[]
    activeKeys: string[]
}>()

const emit = defineEmits<{
    add: [key: string]
    close: []
}>()

const search = ref('')

const availableWidgets = computed(() =>
    props.widgets.filter(
        (w) =>
            !props.activeKeys.includes(w.key) &&
            (search.value === '' ||
                w.label.toLowerCase().includes(search.value.toLowerCase()) ||
                w.description?.toLowerCase().includes(search.value.toLowerCase())),
    ),
)
</script>

<template>
    <div class="widget-picker">
        <div class="picker-header">
            <h3>Add Widget</h3>
            <button class="close-btn" @click="emit('close')">&times;</button>
        </div>
        <input
            v-model="search"
            class="picker-search"
            type="text"
            placeholder="Search widgets..."
        />
        <div class="picker-list">
            <button
                v-for="widget in availableWidgets"
                :key="widget.key"
                class="picker-item"
                @click="emit('add', widget.key)"
            >
                <span v-if="widget.icon" class="picker-icon">{{ widget.icon }}</span>
                <div class="picker-info">
                    <span class="picker-label">{{ widget.label }}</span>
                    <span v-if="widget.description" class="picker-desc">{{ widget.description }}</span>
                </div>
                <span class="picker-type">{{ widget.type }}</span>
            </button>
            <p v-if="availableWidgets.length === 0" class="picker-empty">No widgets available</p>
        </div>
    </div>
</template>

<style scoped>
.widget-picker {
    width: 320px;
    flex-shrink: 0;
    background: var(--card, white);
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    overflow: hidden;
    align-self: flex-start;
}

.picker-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border, #e2e8f0);
}

.picker-header h3 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--foreground, inherit);
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--muted-foreground, #94a3b8);
}

.close-btn:hover {
    color: var(--foreground, inherit);
}

.picker-search {
    width: 100%;
    padding: 0.5rem 1rem;
    border: none;
    border-bottom: 1px solid var(--border, #e2e8f0);
    font-size: 0.875rem;
    outline: none;
    background: var(--card, white);
    color: var(--foreground, inherit);
}

.picker-search::placeholder {
    color: var(--muted-foreground, #94a3b8);
}

.picker-list {
    max-height: 300px;
    overflow-y: auto;
}

.picker-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    width: 100%;
    padding: 0.75rem 1rem;
    border: none;
    background: none;
    cursor: pointer;
    text-align: left;
    color: var(--foreground, inherit);
}

.picker-item:hover {
    background: var(--accent, #f7fafc);
}

.picker-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.picker-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.picker-desc {
    font-size: 0.75rem;
    color: var(--muted-foreground, #94a3b8);
}

.picker-type {
    font-size: 0.625rem;
    text-transform: uppercase;
    color: var(--muted-foreground, #94a3b8);
    background: var(--muted, #f1f5f9);
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
}

.picker-empty {
    padding: 1.5rem;
    text-align: center;
    color: var(--muted-foreground, #94a3b8);
    font-size: 0.875rem;
}
</style>

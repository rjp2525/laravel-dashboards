<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard'
import PeriodSelector from './PeriodSelector.vue'

const { dashboard, isEditing, toggleEditing } = useDashboard()

const emit = defineEmits<{
    'toggle-picker': []
}>()
</script>

<template>
    <div class="dashboard-toolbar">
        <div class="toolbar-left">
            <h1 class="dashboard-title">{{ dashboard?.name ?? 'Dashboard' }}</h1>
        </div>
        <div class="toolbar-right">
            <PeriodSelector />
            <button
                v-if="isEditing"
                class="add-widget-btn"
                @click="emit('toggle-picker')"
            >
                + Add Widget
            </button>
            <button
                class="edit-toggle"
                :class="{ active: isEditing }"
                @click="toggleEditing"
            >
                {{ isEditing ? 'Done' : 'Edit' }}
            </button>
        </div>
    </div>
</template>

<style scoped>
.dashboard-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding: 0.5rem 0;
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dashboard-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    color: var(--foreground, inherit);
}

.edit-toggle {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border, #e2e8f0);
    border-radius: 0.375rem;
    background: var(--card, white);
    color: var(--foreground, inherit);
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}

.edit-toggle:hover {
    background: var(--accent, #f7fafc);
}

.edit-toggle.active {
    background: var(--primary, #0f172a);
    color: var(--primary-foreground, white);
    border-color: var(--primary, #0f172a);
}

.add-widget-btn {
    padding: 0.5rem 1rem;
    border: 1px dashed var(--border, #e2e8f0);
    border-radius: 0.375rem;
    background: var(--card, white);
    color: var(--foreground, inherit);
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}

.add-widget-btn:hover {
    background: var(--accent, #f7fafc);
    border-color: var(--primary, #0f172a);
}
</style>

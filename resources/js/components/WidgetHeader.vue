<script setup lang="ts">
defineProps<{
    label: string
    icon?: string | null
    loading?: boolean
}>()

defineEmits<{
    refresh: []
}>()
</script>

<template>
    <div class="widget-header">
        <div class="widget-title">
            <span v-if="icon" class="widget-icon">{{ icon }}</span>
            <h3>{{ label }}</h3>
        </div>
        <button class="refresh-btn" :class="{ 'is-loading': loading }" title="Refresh" @click="$emit('refresh')">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 2v6h-6" /><path d="M3 12a9 9 0 0 1 15-6.7L21 8" /><path d="M3 22v-6h6" /><path d="M21 12a9 9 0 0 1-15 6.7L3 16" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.375rem;
    flex-shrink: 0;
}

.widget-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.widget-title h3 {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--muted-foreground, #64748b);
    margin: 0;
}

.refresh-btn {
    padding: 0.25rem;
    border: none;
    background: none;
    cursor: pointer;
    color: var(--muted-foreground, #94a3b8);
    border-radius: 0.25rem;
}

.refresh-btn:hover {
    color: var(--foreground, #475569);
    background: var(--accent, #f1f5f9);
}

.refresh-btn.is-loading svg {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

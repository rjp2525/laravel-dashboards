<script setup lang="ts">
defineProps<{
    message: string
}>()

defineEmits<{
    retry: []
}>()
</script>

<template>
    <div class="widget-error">
        <div class="error-icon">!</div>
        <p class="error-message">{{ message }}</p>
        <button class="retry-btn" @click="$emit('retry')">Retry</button>
    </div>
</template>

<style scoped>
.widget-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1;
    padding: 1rem;
    text-align: center;
}

.error-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: #fee2e2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.error-message {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0 0 0.75rem;
}

.retry-btn {
    padding: 0.375rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.25rem;
    background: white;
    cursor: pointer;
    font-size: 0.75rem;
}

.retry-btn:hover {
    background: #f7fafc;
}
</style>

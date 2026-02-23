<template>
    <div class="widget-skeleton">
        <div class="skeleton-line skeleton-lg"></div>
        <div class="skeleton-line skeleton-sm"></div>
    </div>
</template>

<style scoped>
.widget-skeleton {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 0.5rem 0;
    flex: 1;
}

.skeleton-line {
    background: linear-gradient(90deg, var(--muted, #f1f5f9) 25%, var(--border, #e2e8f0) 50%, var(--muted, #f1f5f9) 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 0.25rem;
}

.skeleton-lg {
    height: 2rem;
    width: 60%;
}

.skeleton-sm {
    height: 1rem;
    width: 40%;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { useDashboard } from '@/composables/useDashboard'
import { useFetchClient } from '@/composables/useFetchClient'
import { useGridStack } from '@/composables/useGridStack'
import WidgetWrapper from './WidgetWrapper.vue'
import DashboardToolbar from './DashboardToolbar.vue'
import WidgetPicker from './WidgetPicker.vue'
import type { DashboardPageProps } from '@/types/dashboard'
import type { LayoutItem } from '@/types/widget'

const props = defineProps<DashboardPageProps>()

const { init, layout, gridConfig, isEditing, widgets, updateLayout } = useDashboard()
const { dashboardFetch } = useFetchClient()

const gridContainer = ref<HTMLElement | null>(null)
const showWidgetPicker = ref(false)

init(props)

async function saveLayout() {
    if (!props.dashboard?.slug) return

    try {
        await dashboardFetch(`/${props.dashboard.slug}/layout`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ layout: layout.value }),
        })
    } catch (error) {
        console.error('Failed to save layout:', error)
    }
}

const { grid, updateEditMode } = useGridStack(
    gridContainer,
    gridConfig,
    layout,
    isEditing,
    updateLayout,
)

watch(isEditing, (editing) => {
    updateEditMode(editing)
    if (!editing) {
        showWidgetPicker.value = false
        saveLayout()
    }
})

function handleAddWidget(key: string) {
    if (!grid.value) return

    const widget = widgets.value.find((w) => w.key === key)
    if (!widget) return

    const pos = widget.default_position
    grid.value.addWidget({
        id: widget.key,
        x: pos.x,
        y: pos.y,
        w: pos.w,
        h: pos.h,
    })

    const newItem: LayoutItem = {
        key: widget.key,
        position: { ...pos },
    }
    layout.value = [...layout.value, newItem]
}
</script>

<template>
    <div class="dashboard">
        <DashboardToolbar
            @toggle-picker="showWidgetPicker = !showWidgetPicker"
        />
        <div
            ref="gridContainer"
            class="grid-stack"
        >
            <WidgetWrapper
                v-for="item in layout"
                :key="item.key"
                :layout-item="item"
                :definition="widgets.find((w) => w.key === item.key)"
            />
        </div>
        <Transition name="slide">
            <div
                v-if="showWidgetPicker && isEditing"
                class="picker-overlay"
            >
                <WidgetPicker
                    :widgets="widgets"
                    :active-keys="layout.map((l) => l.key)"
                    @add="handleAddWidget"
                    @close="showWidgetPicker = false"
                />
            </div>
        </Transition>
    </div>
</template>

<style>
@import 'gridstack/dist/gridstack.min.css';

.dashboard {
    padding: 1rem;
    position: relative;
}

.grid-stack {
    min-height: 200px;
}

/* Let GridStack position the item-content, just prevent overflow */
.grid-stack > .grid-stack-item > .grid-stack-item-content {
    overflow: hidden !important;
}

/* Edit mode visual feedback */
.gs-editing .widget-card {
    outline: 2px dashed var(--border, #cbd5e1);
    outline-offset: -2px;
    transition: outline-color 0.15s ease;
}

.gs-editing .widget-card:hover {
    outline-color: var(--primary, #0f172a);
}

/* Hide all scrollbars inside widget cards */
.widget-card,
.widget-card * {
    scrollbar-width: none !important;
    -ms-overflow-style: none !important;
}

.widget-card::-webkit-scrollbar,
.widget-card *::-webkit-scrollbar {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
}

/* Widget picker overlay */
.picker-overlay {
    position: fixed;
    top: 5rem;
    right: 1.5rem;
    z-index: 100;
}

/* Widget picker slide transition */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.2s ease;
}

.slide-enter-from,
.slide-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>

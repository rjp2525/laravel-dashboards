<script setup lang="ts">
import { computed, ref, resolveComponent, type Component } from 'vue'
import type { LayoutItem, WidgetDefinition } from '@/types/widget'
import { useWidget } from '@/composables/useWidget'
import WidgetHeader from './WidgetHeader.vue'
import WidgetSkeleton from './WidgetSkeleton.vue'
import StatWidgetVue from './widgets/StatWidget.vue'
import ChartWidgetVue from './widgets/ChartWidget.vue'
import PieChartWidgetVue from './widgets/PieChartWidget.vue'
import TableWidgetVue from './widgets/TableWidget.vue'
import ListWidgetVue from './widgets/ListWidget.vue'
import ProgressWidgetVue from './widgets/ProgressWidget.vue'
import CustomWidgetVue from './widgets/CustomWidget.vue'
import HeatmapWidgetVue from './widgets/HeatmapWidget.vue'
import StatusTimelineWidgetVue from './widgets/StatusTimelineWidget.vue'
import SparklineWidgetVue from './widgets/SparklineWidget.vue'
import ProgressCircleWidgetVue from './widgets/ProgressCircleWidget.vue'
import BarListWidgetVue from './widgets/BarListWidget.vue'
import FunnelWidgetVue from './widgets/FunnelWidget.vue'
import CategoryWidgetVue from './widgets/CategoryWidget.vue'
import BudgetWidgetVue from './widgets/BudgetWidget.vue'
import GaugeWidgetVue from './widgets/GaugeWidget.vue'

const props = defineProps<{
    layoutItem: LayoutItem
    definition?: WidgetDefinition
}>()

const componentMap: Record<string, any> = {
    StatWidget: StatWidgetVue,
    ChartWidget: ChartWidgetVue,
    PieChartWidget: PieChartWidgetVue,
    TableWidget: TableWidgetVue,
    ListWidget: ListWidgetVue,
    ProgressWidget: ProgressWidgetVue,
    CustomWidget: CustomWidgetVue,
    HeatmapWidget: HeatmapWidgetVue,
    StatusTimelineWidget: StatusTimelineWidgetVue,
    SparklineWidget: SparklineWidgetVue,
    ProgressCircleWidget: ProgressCircleWidgetVue,
    BarListWidget: BarListWidgetVue,
    FunnelWidget: FunnelWidgetVue,
    CategoryWidget: CategoryWidgetVue,
    BudgetWidget: BudgetWidgetVue,
    GaugeWidget: GaugeWidgetVue,
}

const minSizes: Record<string, { w: number; h: number }> = {
    StatWidget: { w: 2, h: 2 },
    ChartWidget: { w: 3, h: 3 },
    PieChartWidget: { w: 3, h: 3 },
    TableWidget: { w: 3, h: 3 },
    ListWidget: { w: 2, h: 2 },
    ProgressWidget: { w: 2, h: 1 },
    CustomWidget: { w: 2, h: 2 },
    HeatmapWidget: { w: 3, h: 3 },
    StatusTimelineWidget: { w: 4, h: 3 },
    SparklineWidget: { w: 2, h: 1 },
    ProgressCircleWidget: { w: 2, h: 2 },
    BarListWidget: { w: 3, h: 2 },
    FunnelWidget: { w: 4, h: 3 },
    CategoryWidget: { w: 2, h: 2 },
    BudgetWidget: { w: 2, h: 1 },
    GaugeWidget: { w: 3, h: 2 },
}

const widgetComponent = computed(() => {
    if (!props.definition) return null
    // Check local map first, then fall back to globally registered components
    // (e.g. custom widgets registered via app.component())
    if (componentMap[props.definition.component]) {
        return componentMap[props.definition.component]
    }
    const resolved = resolveComponent(props.definition.component)
    return typeof resolved === 'string' ? null : resolved
})

const minW = computed(() => {
    if (!props.definition) return 1
    return minSizes[props.definition.component]?.w ?? 2
})

const minH = computed(() => {
    if (!props.definition) return 1
    return minSizes[props.definition.component]?.h ?? 1
})

const { data, loading, refreshing, error, refresh } = props.definition
    ? useWidget(props.definition)
    : { data: ref(null), loading: ref(false), refreshing: ref(false), error: ref('No definition'), refresh: () => {} }
</script>

<template>
    <div
        class="grid-stack-item"
        :gs-id="layoutItem.key"
        :gs-x="layoutItem.position.x"
        :gs-y="layoutItem.position.y"
        :gs-w="layoutItem.position.w"
        :gs-h="layoutItem.position.h"
        :gs-min-w="minW"
        :gs-min-h="minH"
    >
        <div class="grid-stack-item-content">
            <div class="widget-card">
                <WidgetHeader
                    v-if="definition"
                    :label="definition.label"
                    :icon="definition.icon"
                    :loading="loading || refreshing"
                    @refresh="refresh"
                />
                <WidgetSkeleton v-if="loading" />
                <div v-else-if="error" class="widget-error">{{ error }}</div>
                <component
                    v-else-if="widgetComponent && data"
                    :is="widgetComponent"
                    :data="data"
                    :definition="definition"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>
.widget-card {
    position: absolute;
    inset: 6px;
    background: var(--card, white);
    color: var(--card-foreground, inherit);
    border-radius: 0.5rem;
    border: 1px solid var(--border, #e2e8f0);
    padding: 0.625rem 0.75rem;
    display: flex;
    flex-direction: column;
    overflow: hidden;

    /* Semantic color tokens – override in your theme CSS or per-widget inline styles */
    --widget-positive: #16a34a;
    --widget-positive-bg: #dcfce7;
    --widget-positive-fg: #15803d;
    --widget-negative: #dc2626;
    --widget-negative-bg: #fee2e2;
    --widget-negative-fg: #b91c1c;
    --widget-neutral: var(--muted-foreground, #64748b);
    --widget-neutral-bg: var(--muted, #f1f5f9);
    --widget-neutral-fg: var(--muted-foreground, #64748b);
    --widget-warning: #eab308;
    --widget-warning-bg: #fef3c7;
    --widget-warning-fg: #a16207;
    --widget-critical: #b91c1c;
    --widget-critical-bg: #fee2e2;
    --widget-critical-fg: #b91c1c;
}

.widget-error {
    color: var(--widget-negative, #e53e3e);
    font-size: 0.875rem;
    padding: 1rem;
}
</style>

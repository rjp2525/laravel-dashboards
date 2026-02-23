import type { App, Plugin } from 'vue'
import type { ChartRenderer } from '@/types/chart'
import { CHART_RENDERER_KEY } from '@/composables/useChartRenderer'
import Dashboard from '@/components/Dashboard.vue'
import DashboardToolbar from '@/components/DashboardToolbar.vue'
import WidgetWrapper from '@/components/WidgetWrapper.vue'
import WidgetHeader from '@/components/WidgetHeader.vue'
import WidgetSkeleton from '@/components/WidgetSkeleton.vue'
import PeriodSelector from '@/components/PeriodSelector.vue'
import StatWidget from '@/components/widgets/StatWidget.vue'
import ChartWidget from '@/components/widgets/ChartWidget.vue'
import PieChartWidget from '@/components/widgets/PieChartWidget.vue'
import TableWidget from '@/components/widgets/TableWidget.vue'
import ListWidget from '@/components/widgets/ListWidget.vue'
import ProgressWidget from '@/components/widgets/ProgressWidget.vue'
import CustomWidget from '@/components/widgets/CustomWidget.vue'
import HeatmapWidget from '@/components/widgets/HeatmapWidget.vue'
import StatusTimelineWidget from '@/components/widgets/StatusTimelineWidget.vue'
import SparklineWidget from '@/components/widgets/SparklineWidget.vue'
import ProgressCircleWidget from '@/components/widgets/ProgressCircleWidget.vue'
import BarListWidget from '@/components/widgets/BarListWidget.vue'
import FunnelWidget from '@/components/widgets/FunnelWidget.vue'
import CategoryWidget from '@/components/widgets/CategoryWidget.vue'
import BudgetWidget from '@/components/widgets/BudgetWidget.vue'
import GaugeWidget from '@/components/widgets/GaugeWidget.vue'

export { useDashboard } from '@/composables/useDashboard'
export { useWidget } from '@/composables/useWidget'
export { useGridStack } from '@/composables/useGridStack'
export { useWidgetData } from '@/composables/useWidgetData'
export { useFetchClient, onFetchError } from '@/composables/useFetchClient'
export { useEcho } from '@/composables/useEcho'
export { useInertiaPolling } from '@/composables/useInertiaPolling'
export { usePeriod } from '@/composables/usePeriod'
export { useChartRenderer, CHART_RENDERER_KEY } from '@/composables/useChartRenderer'
export { EChartsAdapter } from '@/adapters/EChartsAdapter'
export { ApexChartsAdapter } from '@/adapters/ApexChartsAdapter'
export { ChartJsAdapter } from '@/adapters/ChartJsAdapter'
export { UnovisAdapter } from '@/adapters/UnovisAdapter'

export {
    Dashboard,
    DashboardToolbar,
    WidgetWrapper,
    WidgetHeader,
    WidgetSkeleton,
    PeriodSelector,
    StatWidget,
    ChartWidget,
    PieChartWidget,
    TableWidget,
    ListWidget,
    ProgressWidget,
    CustomWidget,
    HeatmapWidget,
    StatusTimelineWidget,
    SparklineWidget,
    ProgressCircleWidget,
    BarListWidget,
    FunnelWidget,
    CategoryWidget,
    BudgetWidget,
    GaugeWidget,
}

export type * from '@/types/widget'
export type * from '@/types/dashboard'
export type * from '@/types/chart'
export type * from '@/types/echo'
export type { FetchErrorContext, FetchErrorHandler } from '@/composables/useFetchClient'

export interface DashboardPluginOptions {
    renderer?: ChartRenderer
}

const LaravelDashboardPlugin: Plugin<[DashboardPluginOptions?]> = {
    install(app: App, options?: DashboardPluginOptions) {
        const renderer = options?.renderer

        if (renderer) {
            renderer.setup?.(app)
            app.provide(CHART_RENDERER_KEY, renderer)
        }

        app.component('Dashboard', Dashboard)
        app.component('DashboardToolbar', DashboardToolbar)
        app.component('WidgetWrapper', WidgetWrapper)
        app.component('WidgetHeader', WidgetHeader)
        app.component('WidgetSkeleton', WidgetSkeleton)
        app.component('PeriodSelector', PeriodSelector)
        app.component('StatWidget', StatWidget)
        app.component('ChartWidget', ChartWidget)
        app.component('PieChartWidget', PieChartWidget)
        app.component('TableWidget', TableWidget)
        app.component('ListWidget', ListWidget)
        app.component('ProgressWidget', ProgressWidget)
        app.component('CustomWidget', CustomWidget)
        app.component('HeatmapWidget', HeatmapWidget)
        app.component('StatusTimelineWidget', StatusTimelineWidget)
        app.component('SparklineWidget', SparklineWidget)
        app.component('ProgressCircleWidget', ProgressCircleWidget)
        app.component('BarListWidget', BarListWidget)
        app.component('FunnelWidget', FunnelWidget)
        app.component('CategoryWidget', CategoryWidget)
        app.component('BudgetWidget', BudgetWidget)
        app.component('GaugeWidget', GaugeWidget)
    },
}

export default LaravelDashboardPlugin

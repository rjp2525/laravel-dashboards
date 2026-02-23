import { ref, computed, type Ref } from 'vue'
import type { DashboardConfig, DashboardPageProps, GridConfig } from '@/types/dashboard'
import type { LayoutItem, WidgetDefinition } from '@/types/widget'

const dashboard = ref<DashboardConfig | null>(null)
const widgets = ref<WidgetDefinition[]>([])
const layout = ref<LayoutItem[]>([])
const gridConfig = ref<GridConfig | null>(null)
const isEditing = ref(false)
const activePeriod = ref('30d')
const availablePeriods = ref<string[]>([])
const broadcastingEnabled = ref(false)
const realtimeAdapter = ref<'fetch' | 'inertia'>('fetch')
const apiPrefix = ref('/api/dashboard')

export function useDashboard() {
    const init = (props: DashboardPageProps) => {
        dashboard.value = props.dashboard
        widgets.value = props.widgets
        layout.value = props.layout
        gridConfig.value = props.config.grid
        activePeriod.value = props.config.periods.default
        availablePeriods.value = props.config.periods.available
        broadcastingEnabled.value = props.config.broadcasting?.enabled ?? false
        realtimeAdapter.value = props.config.realtime?.adapter ?? 'fetch'
        apiPrefix.value = props.config.routing?.api_prefix ?? '/api/dashboard'
    };

    const toggleEditing = () => {
        isEditing.value = !isEditing.value
    };

    const updateLayout = (newLayout: LayoutItem[]) => {
        layout.value = newLayout
    };

    const widgetMap = computed<Map<string, WidgetDefinition>>(() => {
        const map = new Map<string, WidgetDefinition>()
        widgets.value.forEach((w: WidgetDefinition) => map.set(w.key, w));
        return map
    });

    return {
        dashboard,
        widgets,
        layout,
        gridConfig,
        isEditing,
        activePeriod,
        availablePeriods,
        broadcastingEnabled,
        realtimeAdapter,
        apiPrefix,
        widgetMap,
        init,
        toggleEditing,
        updateLayout,
    }
}

import type { LayoutItem, WidgetDefinition } from './widget'

export interface DashboardConfig {
    id: string
    name: string
    slug: string
    description: string | null
    grid_config: Record<string, unknown> | null
    is_default: boolean
    sort_order: number
}

export interface GridConfig {
    columns: number
    row_height: number
    margin: number
    cell_height: number
    animate: boolean
    float: boolean
    removable: boolean
    disable_resize: boolean
    disable_drag: boolean
}

export interface DashboardPageProps {
    dashboard: DashboardConfig
    widgets: WidgetDefinition[]
    layout: LayoutItem[]
    config: {
        grid: GridConfig
        periods: {
            default: string
            available: string[]
        }
        broadcasting: {
            enabled: boolean
        }
        realtime?: {
            adapter: 'fetch' | 'inertia'
        }
        routing?: {
            api_prefix: string
        }
    }
}

export interface Preset {
    id: string
    name: string
    layout: LayoutItem[]
    is_system: boolean
}

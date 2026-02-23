export interface GridPosition {
    x: number
    y: number
    w: number
    h: number
    min_w?: number
    max_w?: number
    min_h?: number
    max_h?: number
}

export interface RefreshConfig {
    strategy: 'poll' | 'push' | 'inertia' | 'manual'
    interval: number
}

export interface WidgetDefinition {
    key: string
    label: string
    type: 'stat' | 'line' | 'bar' | 'area' | 'pie' | 'donut' | 'table' | 'list' | 'progress' | 'heatmap' | 'status_timeline' | 'custom' | 'sparkline' | 'progress_circle' | 'bar_list' | 'funnel' | 'category' | 'budget' | 'gauge'
    icon: string | null
    description: string | null
    component: string
    default_position: GridPosition
    refresh: RefreshConfig
    filters: string[]
    chart_options?: Record<string, unknown>
}

export interface WidgetData {
    value: unknown
    previous_value: unknown
    change: number | null
    change_percent: number | null
    change_direction: 'positive' | 'negative' | 'neutral'
    series: ChartSeries[]
    labels: string[]
    rows: Record<string, unknown>[]
    columns: (string | { key: string; label: string })[]
    meta: Record<string, unknown>
    updated_at: string | null
}

export interface ChartSeries {
    name: string
    data: (number | { name: string; value: number })[]
    type?: string
    color?: string
}

export interface LayoutItem {
    key: string
    position: GridPosition
}

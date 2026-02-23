import type { App, Component } from 'vue'

export interface ChartConfig {
    type: 'line' | 'bar' | 'area' | 'pie' | 'donut'
    series: SeriesData[]
    labels?: string[]
    options?: Record<string, unknown>
}

export interface SeriesData {
    name: string
    data: (number | { name: string; value: number })[]
    type?: string
    color?: string
}

export interface ChartAdapter {
    buildOptions(config: ChartConfig, isDark?: boolean): Record<string, unknown>
}

export interface ChartRenderer {
    name: string
    component: Component
    adapter: ChartAdapter
    setup?(app: App): void
}

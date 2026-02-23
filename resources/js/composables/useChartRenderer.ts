import type { InjectionKey } from 'vue'
import { inject } from 'vue'
import type { ChartRenderer } from '@/types/chart'

export const CHART_RENDERER_KEY: InjectionKey<ChartRenderer> = Symbol('chart-renderer')

export function useChartRenderer(): ChartRenderer {
    const renderer = inject(CHART_RENDERER_KEY)

    if (!renderer) {
        throw new Error(
            '[LaravelDashboard] No chart renderer provided. ' +
            'Install the plugin with a renderer: app.use(LaravelDashboard, { renderer: EChartsRenderer })',
        )
    }

    return renderer
}

import type { ChartAdapter, ChartConfig } from '@/types/chart'

const DEFAULT_COLORS = [
    '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de',
    '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc',
]

export class UnovisAdapter implements ChartAdapter {
    buildOptions(config: ChartConfig, isDark = false): Record<string, unknown> {
        if (config.type === 'pie' || config.type === 'donut') {
            return this.buildPieOptions(config, isDark)
        }

        return this.buildCartesianOptions(config, isDark)
    }

    private buildCartesianOptions(config: ChartConfig, _isDark: boolean): Record<string, unknown> {
        const chartTypeMap: Record<string, string> = {
            line: 'line',
            bar: 'bar',
            area: 'area',
        }

        // Transform series data into row format: [{ label, seriesA, seriesB, ... }]
        const labels = config.labels ?? []
        const seriesKeys = config.series.map((s) => s.name)
        const data = labels.map((label, i) => {
            const row: Record<string, unknown> = { label }
            config.series.forEach((s) => {
                const val = s.data[i]
                row[s.name] = typeof val === 'object' && val !== null ? (val as { value: number }).value : val
            })
            return row
        })

        // Categories define how each series is rendered
        const categories: Record<string, { name: string; color: string }> = {}
        config.series.forEach((s, i) => {
            categories[s.name] = {
                name: s.name,
                color: s.color ?? DEFAULT_COLORS[i % DEFAULT_COLORS.length],
            }
        })

        return {
            chartType: chartTypeMap[config.type] ?? 'line',
            data,
            categories,
            height: 250,
            // BarChart requires yAxis (keys to plot) and xAxis (label key)
            yAxis: seriesKeys,
            xAxis: 'label',
        }
    }

    private buildPieOptions(config: ChartConfig, _isDark: boolean): Record<string, unknown> {
        const series = config.series[0]
        const data: { name: string; value: number; color: string }[] = []

        if (series) {
            series.data.forEach((item, index) => {
                if (item !== null && typeof item === 'object' && 'name' in item && 'value' in item) {
                    data.push({
                        name: item.name,
                        value: item.value,
                        color: DEFAULT_COLORS[index % DEFAULT_COLORS.length],
                    })
                } else {
                    data.push({
                        name: config.labels?.[index] ?? `Item ${index + 1}`,
                        value: item as number,
                        color: DEFAULT_COLORS[index % DEFAULT_COLORS.length],
                    })
                }
            })
        }

        const categories: Record<string, { name: string; color: string }> = {}
        data.forEach((d) => {
            categories[d.name] = { name: d.name, color: d.color }
        })

        return {
            chartType: 'donut',
            data: data.map((d) => d.value),
            categories,
            height: 250,
        }
    }
}

import type { ChartAdapter, ChartConfig } from '@/types/chart'

const DEFAULT_COLORS = [
    '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de',
    '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc',
]

export class ChartJsAdapter implements ChartAdapter {
    buildOptions(config: ChartConfig, isDark = false): Record<string, unknown> {
        if (config.type === 'pie' || config.type === 'donut') {
            return this.buildPieOptions(config, isDark)
        }

        return this.buildCartesianOptions(config, isDark)
    }

    private buildCartesianOptions(config: ChartConfig, isDark: boolean): Record<string, unknown> {
        const chartTypeMap: Record<string, string> = {
            line: 'line',
            bar: 'bar',
            area: 'line',
        }

        const textColor = isDark ? '#a1a1aa' : '#64748b'
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : '#e2e8f0'

        return {
            chartType: chartTypeMap[config.type] ?? 'line',
            data: {
                labels: config.labels ?? [],
                datasets: config.series.map((s, i) => ({
                    label: s.name,
                    data: s.data,
                    borderColor: s.color ?? DEFAULT_COLORS[i % DEFAULT_COLORS.length],
                    backgroundColor: config.type === 'bar'
                        ? (s.color ?? DEFAULT_COLORS[i % DEFAULT_COLORS.length])
                        : config.type === 'area'
                            ? this.hexToRgba(s.color ?? DEFAULT_COLORS[i % DEFAULT_COLORS.length], 0.2)
                            : 'transparent',
                    fill: config.type === 'area',
                    tension: 0.4,
                })),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { color: textColor },
                        grid: { color: gridColor },
                    },
                    y: {
                        ticks: { color: textColor },
                        grid: { color: gridColor },
                    },
                },
                plugins: {
                    legend: {
                        labels: { color: textColor },
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#27272a' : '#fff',
                        titleColor: isDark ? '#e4e4e7' : '#334155',
                        bodyColor: isDark ? '#e4e4e7' : '#334155',
                        borderColor: isDark ? '#3f3f46' : '#e2e8f0',
                        borderWidth: 1,
                    },
                },
                ...(config.options ?? {}),
            },
        }
    }

    private buildPieOptions(config: ChartConfig, isDark: boolean): Record<string, unknown> {
        const series = config.series[0]
        const labels: string[] = []
        const data: number[] = []

        if (series) {
            series.data.forEach((item, index) => {
                if (item !== null && typeof item === 'object' && 'name' in item && 'value' in item) {
                    labels.push(item.name)
                    data.push(item.value)
                } else {
                    labels.push(config.labels?.[index] ?? `Item ${index + 1}`)
                    data.push(item as number)
                }
            })
        }

        const textColor = isDark ? '#a1a1aa' : '#64748b'

        return {
            chartType: config.type === 'donut' ? 'doughnut' : 'pie',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: DEFAULT_COLORS.slice(0, data.length),
                    borderColor: isDark ? '#27272a' : '#fff',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: textColor },
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#27272a' : '#fff',
                        titleColor: isDark ? '#e4e4e7' : '#334155',
                        bodyColor: isDark ? '#e4e4e7' : '#334155',
                        borderColor: isDark ? '#3f3f46' : '#e2e8f0',
                        borderWidth: 1,
                    },
                },
                ...(config.options ?? {}),
            },
        }
    }

    private hexToRgba(hex: string, alpha: number): string {
        const r = parseInt(hex.slice(1, 3), 16)
        const g = parseInt(hex.slice(3, 5), 16)
        const b = parseInt(hex.slice(5, 7), 16)
        return `rgba(${r}, ${g}, ${b}, ${alpha})`
    }
}

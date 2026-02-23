import type { ChartAdapter, ChartConfig } from '@/types/chart'

export class ApexChartsAdapter implements ChartAdapter {
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
            area: 'area',
        }

        return {
            chartType: chartTypeMap[config.type] ?? 'line',
            chartOptions: {
                chart: {
                    type: chartTypeMap[config.type] ?? 'line',
                    background: 'transparent',
                    toolbar: { show: true },
                    zoom: { enabled: true },
                    ...(isDark ? { foreColor: '#a1a1aa' } : {}),
                },
                theme: {
                    mode: isDark ? 'dark' : 'light',
                },
                xaxis: {
                    categories: config.labels ?? [],
                    labels: {
                        style: { colors: isDark ? '#a1a1aa' : '#64748b' },
                    },
                },
                yaxis: {
                    labels: {
                        style: { colors: isDark ? '#a1a1aa' : '#64748b' },
                    },
                },
                grid: {
                    borderColor: isDark ? 'rgba(255,255,255,0.08)' : '#e2e8f0',
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                },
                stroke: {
                    curve: 'smooth',
                    width: 2,
                },
                ...(config.options ?? {}),
            },
            series: config.series.map((s) => ({
                name: s.name,
                data: s.data,
                ...(s.color ? { color: s.color } : {}),
            })),
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

        return {
            chartType: config.type === 'donut' ? 'donut' : 'pie',
            chartOptions: {
                chart: {
                    type: config.type === 'donut' ? 'donut' : 'pie',
                    background: 'transparent',
                    ...(isDark ? { foreColor: '#a1a1aa' } : {}),
                },
                theme: {
                    mode: isDark ? 'dark' : 'light',
                },
                labels,
                legend: {
                    labels: {
                        colors: isDark ? '#e4e4e7' : '#334155',
                    },
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                },
                ...(config.options ?? {}),
            },
            series: data,
        }
    }
}

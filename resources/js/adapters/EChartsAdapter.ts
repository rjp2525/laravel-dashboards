import type { ChartAdapter, ChartConfig } from '@/types/chart'

export class EChartsAdapter implements ChartAdapter {
    buildOptions(config: ChartConfig, isDark = false): Record<string, unknown> {
        const textColor = isDark ? '#a1a1aa' : '#64748b'
        const splitLineColor = isDark ? 'rgba(255,255,255,0.08)' : '#e2e8f0'

        const baseOptions: Record<string, unknown> = {
            tooltip: {
                trigger: 'axis',
                backgroundColor: isDark ? '#27272a' : '#fff',
                borderColor: isDark ? '#3f3f46' : '#e2e8f0',
                textStyle: { color: isDark ? '#e4e4e7' : '#334155' },
            },
            grid: {
                left: '3%',
                right: '4%',
                bottom: config.type !== 'pie' && config.type !== 'donut' ? '15%' : '3%',
                containLabel: true,
            },
        }

        if (config.type === 'pie' || config.type === 'donut') {
            return this.buildPieOptions(config, baseOptions, isDark, textColor)
        }

        return this.buildCartesianOptions(config, baseOptions, isDark, textColor, splitLineColor)
    }

    private buildCartesianOptions(
        config: ChartConfig,
        baseOptions: Record<string, unknown>,
        isDark: boolean,
        textColor: string,
        splitLineColor: string,
    ): Record<string, unknown> {
        const chartTypeMap: Record<string, string> = {
            line: 'line',
            bar: 'bar',
            area: 'line',
        }

        return {
            ...baseOptions,
            xAxis: {
                type: 'category',
                data: config.labels ?? [],
                axisLabel: { color: textColor },
                axisLine: { lineStyle: { color: splitLineColor } },
            },
            yAxis: {
                type: 'value',
                axisLabel: { color: textColor },
                splitLine: { lineStyle: { color: splitLineColor } },
            },
            series: config.series.map((s) => ({
                name: s.name,
                type: chartTypeMap[config.type] ?? 'line',
                data: s.data,
                smooth: true,
                ...(config.type === 'area' ? { areaStyle: {} } : {}),
                ...(s.color ? { itemStyle: { color: s.color } } : {}),
            })),
            dataZoom: [
                {
                    type: 'slider',
                    show: true,
                    start: 0,
                    end: 100,
                    height: 20,
                    bottom: 0,
                    borderColor: isDark ? '#3f3f46' : '#e2e8f0',
                    backgroundColor: 'transparent',
                    fillerColor: isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)',
                    dataBackground: {
                        lineStyle: { color: isDark ? '#52525b' : '#cbd5e1' },
                        areaStyle: { color: isDark ? 'rgba(255,255,255,0.02)' : 'rgba(0,0,0,0.02)' },
                    },
                    selectedDataBackground: {
                        lineStyle: { color: isDark ? '#71717a' : '#94a3b8' },
                        areaStyle: { color: isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.04)' },
                    },
                    handleStyle: {
                        color: isDark ? '#52525b' : '#94a3b8',
                        borderColor: isDark ? '#71717a' : '#64748b',
                    },
                    textStyle: { color: textColor },
                },
            ],
            ...(config.options ?? {}),
        }
    }

    private buildPieOptions(
        config: ChartConfig,
        baseOptions: Record<string, unknown>,
        isDark: boolean,
        textColor: string,
    ): Record<string, unknown> {
        const series = config.series[0]
        let data: { value: unknown; name: string }[] = []

        if (series) {
            const first = series.data[0]
            if (first !== null && typeof first === 'object' && 'name' in first && 'value' in first) {
                data = series.data as unknown as { value: unknown; name: string }[]
            } else {
                data = series.data.map((value, index) => ({
                    value,
                    name: config.labels?.[index] ?? `Item ${index + 1}`,
                }))
            }
        }

        return {
            ...baseOptions,
            tooltip: {
                trigger: 'item',
                backgroundColor: isDark ? '#27272a' : '#fff',
                borderColor: isDark ? '#3f3f46' : '#e2e8f0',
                textStyle: { color: isDark ? '#e4e4e7' : '#334155' },
            },
            series: [
                {
                    type: 'pie',
                    radius: config.type === 'donut' ? ['40%', '70%'] : '70%',
                    data,
                    label: {
                        color: isDark ? '#e4e4e7' : '#334155',
                    },
                },
            ],
            ...(config.options ?? {}),
        }
    }
}

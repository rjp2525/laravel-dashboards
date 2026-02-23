import { use } from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart, BarChart, PieChart, HeatmapChart } from 'echarts/charts'
import {
    TitleComponent,
    TooltipComponent,
    GridComponent,
    LegendComponent,
    VisualMapComponent,
    DataZoomComponent,
} from 'echarts/components'
import { EChartsAdapter } from '@/adapters/EChartsAdapter'
import EChartsChart from '@/renderers/components/EChartsChart.vue'
import type { ChartRenderer } from '@/types/chart'

export const EChartsRenderer: ChartRenderer = {
    name: 'echarts',
    component: EChartsChart,
    adapter: new EChartsAdapter(),
    setup() {
        use([
            CanvasRenderer,
            LineChart,
            BarChart,
            PieChart,
            HeatmapChart,
            TitleComponent,
            TooltipComponent,
            GridComponent,
            LegendComponent,
            VisualMapComponent,
            DataZoomComponent,
        ])
    },
}

import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Filler,
    Tooltip,
    Legend,
} from 'chart.js'
import { ChartJsAdapter } from '@/adapters/ChartJsAdapter'
import ChartJsChart from '@/renderers/components/ChartJsChart.vue'
import type { ChartRenderer } from '@/types/chart'

export const ChartJsRenderer: ChartRenderer = {
    name: 'chartjs',
    component: ChartJsChart,
    adapter: new ChartJsAdapter(),
    setup() {
        ChartJS.register(
            CategoryScale,
            LinearScale,
            PointElement,
            LineElement,
            BarElement,
            ArcElement,
            Filler,
            Tooltip,
            Legend,
        )
    },
}

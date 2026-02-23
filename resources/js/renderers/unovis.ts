import { UnovisAdapter } from '@/adapters/UnovisAdapter'
import UnovisChart from '@/renderers/components/UnovisChart.vue'
import type { ChartRenderer } from '@/types/chart'

export const UnovisRenderer: ChartRenderer = {
    name: 'unovis',
    component: UnovisChart,
    adapter: new UnovisAdapter(),
}

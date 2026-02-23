import type { App } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { ApexChartsAdapter } from '@/adapters/ApexChartsAdapter'
import ApexChartsChart from '@/renderers/components/ApexChartsChart.vue'
import type { ChartRenderer } from '@/types/chart'

let installed = false

export const ApexChartsRenderer: ChartRenderer = {
    name: 'apexcharts',
    component: ApexChartsChart,
    adapter: new ApexChartsAdapter(),
    setup(app: App) {
        if (installed) return
        installed = true
        app.component('apexchart', VueApexCharts)
    },
}

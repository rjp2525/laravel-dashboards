// Type declarations for optional peer dependencies.
// These allow the build to succeed without the packages installed.

declare module 'vue3-apexcharts' {
    import type { DefineComponent } from 'vue'
    const component: DefineComponent
    export default component
}

declare module 'vue-chartjs' {
    import type { DefineComponent } from 'vue'
    export const Line: DefineComponent
    export const Bar: DefineComponent
    export const Pie: DefineComponent
    export const Doughnut: DefineComponent
}

declare module 'chart.js' {
    export class Chart {
        static register(...items: unknown[]): void
    }
    export const CategoryScale: unknown
    export const LinearScale: unknown
    export const PointElement: unknown
    export const LineElement: unknown
    export const BarElement: unknown
    export const ArcElement: unknown
    export const Filler: unknown
    export const Tooltip: unknown
    export const Legend: unknown
}

declare module 'vue-chrts' {
    import type { DefineComponent } from 'vue'
    export const LineChart: DefineComponent
    export const BarChart: DefineComponent
    export const AreaChart: DefineComponent
    export const DonutChart: DefineComponent
}

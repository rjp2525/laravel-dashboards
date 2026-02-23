import { computed } from 'vue'
import { useDashboard } from './useDashboard'

export interface PeriodOption {
    value: string
    label: string
}

const PERIOD_LABELS: Record<string, string> = {
    today: 'Today',
    '7d': 'Last 7 Days',
    '30d': 'Last 30 Days',
    '90d': 'Last 90 Days',
    ytd: 'Year to Date',
    '1y': 'Last Year',
    custom: 'Custom Range',
}

export const usePeriod = () => {
    const { activePeriod, availablePeriods } = useDashboard()

    const periods = computed<PeriodOption[]>(() =>
        availablePeriods.value.map((p: string) => ({
            value: p,
            label: PERIOD_LABELS[p] ?? p,
        })),
    )

    const setPeriod = (period: string) => {
        activePeriod.value = period
    };

    return {
        activePeriod,
        periods,
        setPeriod,
    };
};

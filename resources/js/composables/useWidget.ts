import { ref, onMounted, onUnmounted, watch, type Ref } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'
import { useWidgetData } from './useWidgetData'
import { useDashboard } from './useDashboard'
import { useEcho } from './useEcho'
import { useInertiaPolling } from './useInertiaPolling'

export const useWidget = (definition: WidgetDefinition) => {
    const { fetchWidgetData } = useWidgetData()
    const { activePeriod, dashboard, broadcastingEnabled, realtimeAdapter } = useDashboard()

    const data = ref<WidgetData | null>(null)
    const loading = ref(true)
    const refreshing = ref(false)
    const error = ref<string | null>(null)

    let pollTimer: ReturnType<typeof setInterval> | null = null
    const cleanups: (() => void)[] = []

    async function refresh() {
        // Show skeleton on initial load, spinner on subsequent refreshes
        if (!data.value) {
            loading.value = true
        } else {
            refreshing.value = true
        }
        error.value = null

        try {
            data.value = await fetchWidgetData(definition.key, activePeriod.value)
        } catch (e) {
            const message = e instanceof Error ? e.message : 'Failed to load widget data'
            // 304 Not Modified means data hasn't changed — keep existing data
            if (message !== 'NOT_MODIFIED') {
                error.value = data.value ? null : message
            }
        } finally {
            loading.value = false
            refreshing.value = false
        }
    };

    const startPolling = () => {
        stopPolling()

        if (definition.refresh.strategy === 'poll' && definition.refresh.interval > 0) {
            pollTimer = setInterval(refresh, definition.refresh.interval * 1000)
        }
    };

    const stopPolling = () => {
        if (pollTimer) {
            clearInterval(pollTimer)
            pollTimer = null
        }
    };

    const resolveStrategy = (): 'push' | 'inertia' | 'poll' | 'manual' => {
        const strategy = definition.refresh.strategy

        // Push strategy requires broadcasting to be enabled
        if (strategy === 'push' && broadcastingEnabled.value) {
            return 'push'
        }

        // Explicit inertia strategy on the widget
        if (strategy === 'inertia') {
            return 'inertia'
        }

        // Poll widgets can be upgraded to inertia if the global adapter is set
        if (strategy === 'poll' && realtimeAdapter.value === 'inertia') {
            return 'inertia'
        }

        if (strategy === 'manual') {
            return 'manual'
        }

        // Default to poll (also fallback when push is set but broadcasting disabled)
        return 'poll'
    }

    const setupStrategy = () => {
        const resolved = resolveStrategy()

        switch (resolved) {
            case 'push': {
                const slug = dashboard.value?.slug ?? ''
                const { disconnect } = useEcho(slug, 'dashboard', (widgetKey, widgetData) => {
                    if (widgetKey === definition.key) {
                        data.value = widgetData as WidgetData
                    }
                })
                cleanups.push(disconnect)

                // Optional fallback polling for push strategy if interval is set
                if (definition.refresh.interval > 0) {
                    pollTimer = setInterval(refresh, definition.refresh.interval * 1000)
                }
                break
            }
            case 'inertia': {
                const interval = definition.refresh.interval > 0 ? definition.refresh.interval : 60
                const { start, stop } = useInertiaPolling(interval)
                start()
                cleanups.push(stop)
                break
            }
            case 'poll': {
                startPolling()
                break
            }
            case 'manual':
                // No auto-refresh
                break
        }
    }

    onMounted((): void => {
        refresh()
        setupStrategy()
    });

    onUnmounted((): void => {
        stopPolling()
        cleanups.forEach((fn) => fn())
    });

    watch(activePeriod, (): void => {
        refresh()
    });

    return {
        data,
        loading,
        refreshing,
        error,
        refresh,
    };
}

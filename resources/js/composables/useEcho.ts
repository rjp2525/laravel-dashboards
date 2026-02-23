import { ref, onMounted, onUnmounted } from 'vue'

interface WidgetUpdatedPayload {
    widget_key: string
    data: unknown
}

export function useEcho(
    dashboardSlug: string,
    channelPrefix: string,
    onWidgetUpdated: (widgetKey: string, data: unknown) => void,
) {
    const connected = ref(false)
    let channelName: string | null = null

    const connect = () => {
        if (!window.Echo) {
            console.warn(
                '[laravel-dashboards] window.Echo is not available. ' +
                'Install and configure laravel-echo in your app bootstrap to enable push updates.',
            )
            return
        }

        channelName = `${channelPrefix}.${dashboardSlug}`

        window.Echo.private(channelName).listen('.widget.updated', (raw: unknown) => {
            const payload = raw as WidgetUpdatedPayload
            onWidgetUpdated(payload.widget_key, payload.data)
        })

        connected.value = true
    }

    const disconnect = () => {
        if (channelName && window.Echo) {
            window.Echo.leave(channelName)
            channelName = null
        }
        connected.value = false
    }

    onMounted(connect)
    onUnmounted(disconnect)

    return {
        connected,
        disconnect,
    }
}

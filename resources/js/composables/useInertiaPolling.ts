import { onUnmounted } from 'vue'

let routerModule: { router: { reload: (options: Record<string, unknown>) => void } } | null = null

async function getRouter() {
    if (!routerModule) {
        try {
            routerModule = await import('@inertiajs/vue3')
        } catch {
            console.warn(
                '[laravel-dashboards] @inertiajs/vue3 is not available. ' +
                'Install @inertiajs/vue3 to use the Inertia polling adapter.',
            )
            return null
        }
    }
    return routerModule.router
}

export function useInertiaPolling(intervalSeconds: number) {
    let timer: ReturnType<typeof setInterval> | null = null

    const start = async () => {
        stop()

        if (intervalSeconds <= 0) return

        const router = await getRouter()
        if (!router) return

        timer = setInterval(() => {
            router.reload({
                only: ['widgets'],
                preserveState: true,
                preserveScroll: true,
            })
        }, intervalSeconds * 1000)
    }

    const stop = () => {
        if (timer) {
            clearInterval(timer)
            timer = null
        }
    }

    onUnmounted(stop)

    return { start, stop }
}

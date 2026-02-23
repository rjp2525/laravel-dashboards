import { ref } from 'vue'
import type { WidgetData } from '@/types/widget'
import { useFetchClient } from './useFetchClient'

export function useWidgetData() {
    const { dashboardFetch } = useFetchClient()
    const etags = ref<Map<string, string>>(new Map())

    async function fetchWidgetData(
        widgetKey: string,
        period: string,
        filters: Record<string, unknown> = {},
    ): Promise<WidgetData> {
        const params = new URLSearchParams({
            period,
            ...Object.fromEntries(
                Object.entries(filters).map(([k, v]) => [`filters[${k}]`, String(v)]),
            ),
        })

        const headers: Record<string, string> = {}

        const etagKey = `${widgetKey}:${period}`
        const cachedEtag = etags.value.get(etagKey)
        if (cachedEtag) {
            headers['If-None-Match'] = cachedEtag
        }

        const response = await dashboardFetch(`/widgets/${widgetKey}/data?${params}`, {
            headers,
        })

        if (response.status === 304) {
            throw new Error('NOT_MODIFIED')
        }

        const etag = response.headers.get('ETag')
        if (etag) {
            etags.value.set(etagKey, etag)
        }

        const json = await response.json()
        return json.data as WidgetData
    }

    return {
        fetchWidgetData,
    }
}

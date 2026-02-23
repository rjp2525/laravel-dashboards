import { useDashboard } from './useDashboard'

export interface FetchErrorContext {
    url: string
    method: string
    status?: number
    statusText?: string
    body?: unknown
    error: Error
}

export type FetchErrorHandler = (context: FetchErrorContext) => void

const errorHandlers: FetchErrorHandler[] = []

export function onFetchError(handler: FetchErrorHandler): () => void {
    errorHandlers.push(handler)
    return () => {
        const idx = errorHandlers.indexOf(handler)
        if (idx > -1) errorHandlers.splice(idx, 1)
    }
}

function getXsrfToken(): string | undefined {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
    return match ? decodeURIComponent(match[1]) : undefined
}

export function useFetchClient() {
    const { apiPrefix } = useDashboard()

    async function dashboardFetch(path: string, options: RequestInit = {}): Promise<Response> {
        const url = `${apiPrefix.value}${path}`
        const method = (options.method ?? 'GET').toUpperCase()

        const headers: Record<string, string> = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(options.headers as Record<string, string> ?? {}),
        }

        const xsrfToken = getXsrfToken()
        if (xsrfToken) {
            headers['X-XSRF-TOKEN'] = xsrfToken
        }

        const response = await fetch(url, {
            ...options,
            method,
            headers,
            credentials: 'same-origin',
        })

        if (!response.ok && response.status !== 304) {
            let body: unknown
            try {
                body = await response.clone().json()
            } catch {
                // response body is not JSON — leave body undefined
            }

            const error = new Error(`Request failed: ${method} ${url} (${response.status} ${response.statusText})`)

            const context: FetchErrorContext = {
                url,
                method,
                status: response.status,
                statusText: response.statusText,
                body,
                error,
            }

            for (const handler of errorHandlers) {
                handler(context)
            }

            throw error
        }

        return response
    }

    return { dashboardFetch }
}

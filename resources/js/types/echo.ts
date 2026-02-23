interface EchoChannel {
    listen(event: string, callback: (data: unknown) => void): EchoChannel
    stopListening(event: string): EchoChannel
}

interface Echo {
    channel(name: string): EchoChannel
    private(name: string): EchoChannel
    leave(name: string): void
}

declare global {
    interface Window {
        Echo?: Echo
    }
}

export type { Echo, EchoChannel }

import { ref, onMounted, onUnmounted } from 'vue'

const isDark = ref(false)
let observerCount = 0
let observer: MutationObserver | null = null

function checkDark(): void {
    isDark.value = document.documentElement.classList.contains('dark')
}

export function useDarkMode() {
    onMounted(() => {
        checkDark()
        observerCount++

        if (!observer) {
            observer = new MutationObserver(() => checkDark())
            observer.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            })
        }
    })

    onUnmounted(() => {
        observerCount--
        if (observerCount <= 0 && observer) {
            observer.disconnect()
            observer = null
            observerCount = 0
        }
    })

    return { isDark }
}

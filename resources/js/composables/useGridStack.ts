import { ref, onMounted, onUnmounted, nextTick, type Ref } from 'vue'
import { GridStack, type GridStackNode } from 'gridstack'
import type { GridConfig } from '@/types/dashboard'
import type { LayoutItem } from '@/types/widget'

export function useGridStack(
    containerRef: Ref<HTMLElement | null>,
    gridConfig: Ref<GridConfig | null>,
    layout: Ref<LayoutItem[]>,
    isEditing: Ref<boolean>,
    onLayoutChange: (layout: LayoutItem[]) => void,
) {
    const grid = ref<GridStack | null>(null)

    const initGrid = () => {
        if (!containerRef.value || !gridConfig.value) return

        grid.value = GridStack.init(
            {
                column: gridConfig.value.columns,
                cellHeight: gridConfig.value.cell_height,
                margin: 0,
                animate: gridConfig.value.animate,
                float: gridConfig.value.float,
                removable: gridConfig.value.removable,
                staticGrid: true,
            },
            containerRef.value,
        )

        grid.value.on('change', (_event: Event, items: GridStackNode[]) => {
            const changedMap = new Map(
                items.map((item) => [
                    item.id as string,
                    {
                        key: item.id as string,
                        position: {
                            x: item.x ?? 0,
                            y: item.y ?? 0,
                            w: item.w ?? 4,
                            h: item.h ?? 2,
                        },
                    },
                ]),
            )
            const merged = layout.value.map(
                (existing) => changedMap.get(existing.key) ?? existing,
            )
            onLayoutChange(merged)
        })
    };

    const destroyGrid = () => {
        if (grid.value) {
            grid.value.destroy(false)
            grid.value = null
        }
    };

    const updateEditMode = (editing: boolean) => {
        // Toggle class directly on DOM — do NOT call setStatic() as it
        // conflicts with Vue's DOM management and destroys the grid layout.
        containerRef.value?.classList.toggle('gs-editing', editing)
    };

    onMounted(async (): Promise<void> => {
        await nextTick()
        initGrid()
    });

    onUnmounted((): void => {
        destroyGrid();
    });

    return {
        grid,
        updateEditMode,
        destroyGrid,
    }
}

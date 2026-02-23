<script setup lang="ts">
import { ref, computed } from 'vue'
import type { WidgetData, WidgetDefinition } from '@/types/widget'

interface ColumnDef {
    key: string
    label: string
}

const props = defineProps<{
    data: WidgetData
    definition: WidgetDefinition
}>()

const currentPage = ref(1)
const perPage = computed(() => (props.definition as any).per_page ?? 10)

const paginatedRows = computed(() => {
    const start = (currentPage.value - 1) * perPage.value
    return props.data.rows.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(props.data.rows.length / perPage.value))

const columns = computed<ColumnDef[]>(() => {
    if (props.data.columns.length > 0) {
        return props.data.columns.map((col: any) => {
            if (typeof col === 'string') return { key: col, label: col }
            return { key: col.key, label: col.label ?? col.key }
        })
    }
    if (props.data.rows.length > 0) {
        return Object.keys(props.data.rows[0]).map((k) => ({ key: k, label: k }))
    }
    return []
})
</script>

<template>
    <div class="table-widget">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, idx) in paginatedRows" :key="idx">
                        <td v-for="col in columns" :key="col.key">{{ row[col.key] ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="totalPages > 1" class="table-pagination">
            <button :disabled="currentPage <= 1" @click="currentPage--">Prev</button>
            <span>{{ currentPage }} / {{ totalPages }}</span>
            <button :disabled="currentPage >= totalPages" @click="currentPage++">Next</button>
        </div>
    </div>
</template>

<style scoped>
.table-widget { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.table-container { flex: 1; overflow: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
th { text-align: left; padding: 0.5rem; border-bottom: 2px solid var(--border, #e2e8f0); font-weight: 600; color: var(--muted-foreground, #475569); white-space: nowrap; }
td { padding: 0.5rem; border-bottom: 1px solid var(--border, #f1f5f9); color: var(--foreground, inherit); max-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
tr:hover td { background: var(--accent, #f8fafc); }
.table-pagination { display: flex; align-items: center; justify-content: center; gap: 0.75rem; padding: 0.5rem; border-top: 1px solid var(--border, #e2e8f0); }
.table-pagination button { padding: 0.25rem 0.5rem; border: 1px solid var(--border, #e2e8f0); border-radius: 0.25rem; background: var(--card, white); color: var(--foreground, inherit); cursor: pointer; font-size: 0.75rem; }
.table-pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
.table-pagination span { font-size: 0.75rem; color: var(--muted-foreground, #64748b); }
</style>

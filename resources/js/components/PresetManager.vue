<script setup lang="ts">
import { ref, onMounted } from 'vue'
import type { Preset } from '@/types/dashboard'
import { useDashboard } from '@/composables/useDashboard'
import { useFetchClient } from '@/composables/useFetchClient'

const { dashboard, layout, updateLayout } = useDashboard()
const { dashboardFetch } = useFetchClient()

const presets = ref<Preset[]>([])
const showSaveDialog = ref(false)
const newPresetName = ref('')

async function loadPresets() {
    if (!dashboard.value) return
    try {
        const res = await dashboardFetch(`/${dashboard.value.slug}/presets`)
        const json = await res.json()
        presets.value = json.data ?? json
    } catch (error) {
        console.error('Failed to load presets:', error)
    }
}

async function applyPreset(presetId: string) {
    if (!dashboard.value) return
    try {
        await dashboardFetch(`/${dashboard.value.slug}/presets/${presetId}/apply`, {
            method: 'POST',
        })
        const preset = presets.value.find((p) => p.id === presetId)
        if (preset) {
            updateLayout(preset.layout)
        }
    } catch (error) {
        console.error('Failed to apply preset:', error)
    }
}

async function savePreset() {
    if (!dashboard.value || !newPresetName.value) return
    try {
        await dashboardFetch(`/${dashboard.value.slug}/presets`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name: newPresetName.value, layout: layout.value }),
        })
        newPresetName.value = ''
        showSaveDialog.value = false
        await loadPresets()
    } catch (error) {
        console.error('Failed to save preset:', error)
    }
}

async function deletePreset(presetId: string) {
    if (!dashboard.value) return
    try {
        await dashboardFetch(`/${dashboard.value.slug}/presets/${presetId}`, {
            method: 'DELETE',
        })
        await loadPresets()
    } catch (error) {
        console.error('Failed to delete preset:', error)
    }
}

onMounted(loadPresets)
</script>

<template>
    <div class="preset-manager">
        <div class="preset-list">
            <button
                v-for="preset in presets"
                :key="preset.id"
                class="preset-item"
                @click="applyPreset(preset.id)"
            >
                <span>{{ preset.name }}</span>
                <span v-if="preset.is_system" class="preset-badge">System</span>
                <button
                    v-if="!preset.is_system"
                    class="preset-delete"
                    @click.stop="deletePreset(preset.id)"
                >
                    &times;
                </button>
            </button>
        </div>
        <button class="save-preset-btn" @click="showSaveDialog = true">Save as Preset</button>
        <div v-if="showSaveDialog" class="save-dialog">
            <input v-model="newPresetName" placeholder="Preset name..." class="preset-input" />
            <button class="save-btn" @click="savePreset">Save</button>
            <button class="cancel-btn" @click="showSaveDialog = false">Cancel</button>
        </div>
    </div>
</template>

<style scoped>
.preset-manager { display: flex; flex-direction: column; gap: 0.5rem; }
.preset-list { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.preset-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; background: white; cursor: pointer; font-size: 0.875rem; }
.preset-item:hover { background: #f7fafc; }
.preset-badge { font-size: 0.625rem; text-transform: uppercase; color: #94a3b8; background: #f1f5f9; padding: 0.125rem 0.375rem; border-radius: 0.125rem; }
.preset-delete { background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1rem; padding: 0; }
.save-preset-btn { padding: 0.375rem 0.75rem; border: 1px dashed #e2e8f0; border-radius: 0.25rem; background: none; cursor: pointer; font-size: 0.875rem; color: #64748b; align-self: flex-start; }
.save-dialog { display: flex; gap: 0.5rem; align-items: center; }
.preset-input { padding: 0.375rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; font-size: 0.875rem; }
.save-btn { padding: 0.375rem 0.75rem; border: none; border-radius: 0.25rem; background: #0f172a; color: white; cursor: pointer; font-size: 0.875rem; }
.cancel-btn { padding: 0.375rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.25rem; background: white; cursor: pointer; font-size: 0.875rem; }
</style>

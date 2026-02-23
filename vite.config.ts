import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import dts from 'vite-plugin-dts'
import { resolve } from 'path'

export default defineConfig({
    plugins: [
        vue(),
        dts({
            insertTypesEntry: true,
            include: ['resources/js/**/*.ts', 'resources/js/**/*.vue'],
        }),
    ],
    build: {
        lib: {
            entry: {
                index: resolve(__dirname, 'resources/js/plugin.ts'),
                'renderers/echarts': resolve(__dirname, 'resources/js/renderers/echarts.ts'),
                'renderers/apexcharts': resolve(__dirname, 'resources/js/renderers/apexcharts.ts'),
                'renderers/chartjs': resolve(__dirname, 'resources/js/renderers/chartjs.ts'),
                'renderers/unovis': resolve(__dirname, 'resources/js/renderers/unovis.ts'),
            },
            formats: ['es'],
        },
        rollupOptions: {
            external: [
                'vue',
                '@inertiajs/vue3',
                'gridstack',
                'echarts',
                'echarts/core',
                'echarts/renderers',
                'echarts/charts',
                'echarts/components',
                'vue-echarts',
                'apexcharts',
                'vue3-apexcharts',
                'chart.js',
                'vue-chartjs',
                '@unovis/ts',
                '@unovis/vue',
                'vue-chrts',
                'laravel-echo',
            ],
            output: {
                globals: {
                    vue: 'Vue',
                },
            },
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, 'resources/js'),
        },
    },
})

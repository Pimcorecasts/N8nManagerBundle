import {fileURLToPath, URL} from 'node:url'

import {defineConfig} from 'vite'
import vue from '@vitejs/plugin-vue'

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue({
            template: {
                compilerOptions: {
                    delimiters: ['${', '}']
                }
            }
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    },
    build: {
        manifest: true,
        rollupOptions: {
            input: {
                n8nManager: fileURLToPath(new URL('./src/main.js', import.meta.url))
            },
            output: {
                dir: fileURLToPath(new URL('./dist', import.meta.url)),
                entryFileNames: '[name].js',
                chunkFileNames: '[name]-[hash].js',
                assetFileNames: '[name]-[hash].[ext]'
            }
        },
        watch: {
            include: 'src/**'
        }
    }
})

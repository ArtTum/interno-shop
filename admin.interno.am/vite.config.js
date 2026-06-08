import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import tailwindcss from 'tailwindcss'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue()
    ],
    css: {
        postcss: {
            plugins: [tailwindcss()]
        }
    },
    build: {
        target: 'esnext',
        minify: 'esbuild',
        sourcemap: true,
        chunkSizeWarningLimit: 8000,
    },
    cacheDir: './node_modules/.vite_cache',
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@pages': path.resolve(__dirname, 'resources/js/pages'),
            '@store': path.resolve(__dirname, 'resources/js/store'),
            '@router': path.resolve(__dirname, 'resources/js/router'),
            '@layouts': path.resolve(__dirname, 'resources/js/layouts'),
            '@assets': path.resolve(__dirname, 'resources/js/assets'),
            '@validation': path.resolve(__dirname, 'resources/js/validation'),
        },
    },
    optimizeDeps: {
        include: ['vue', 'vue-index'],
    }
});

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/web/app.scss',
                'resources/js/web/app.js',
                'resources/sass/dashboard/app.scss',
                'resources/js/dashboard/app.js',
            ],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // Keep absolute urls (/assets/...) as they are; don't import them.
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js/dashboard', import.meta.url)),
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                // The stylesheets still use @import; migrating to @use/@forward is a separate refactor.
                silenceDeprecations: ['import'],
            },
        },
    },
});

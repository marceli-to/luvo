import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/web/app.scss',
                'resources/js/web/app.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        preprocessorOptions: {
            scss: {
                // The stylesheets still use @import; migrating to @use/@forward is a separate refactor.
                silenceDeprecations: ['import'],
            },
        },
    },
});

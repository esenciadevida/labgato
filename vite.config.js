import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        minify: 'oxc',
        cssMinify: true,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/alpinejs') || id.includes('node_modules/@alpinejs')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

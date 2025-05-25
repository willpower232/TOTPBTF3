import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/fab/app.scss',
                'resources/sass/fab/critical.scss',
                'resources/js/app.js',
            ],
            refresh: true,
            publicDirectory: 'public_html',
        }),
    ],
    // server: {
    //     host: true,
    //     hmr: {
    //         host: 'localhost',
    //     }
    // },
});

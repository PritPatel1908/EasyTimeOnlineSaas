import { defineConfig } from 'vite';
import { cpSync } from 'node:fs';
import { resolve } from 'node:path';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

function copyAttendanceAssets() {
    return {
        name: 'copy-attendance-assets',
        closeBundle() {
            for (const directory of ['css', 'img', 'plugins', 'js']) {
                cpSync(
                    resolve('resources', directory),
                    resolve('public/build', directory),
                    { recursive: true },
                );
            }
        },
    };
}

export default defineConfig({
    publicDir: false,
    plugins: [
        copyAttendanceAssets(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/admin.css',
                'resources/js/admin.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});

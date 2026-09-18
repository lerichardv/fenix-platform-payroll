import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/common.js',
                'resources/js/reports/reports.js',
                'resources/js/reports/piece-rate.js',
                'resources/js/reports/misc-piece-rate.js',
                'resources/js/reports/location-executive.js',
                'resources/js/reports/sign-sheets-report.js',
                'resources/js/reports/estimated-payroll.js',
                'resources/js/reports/dynamic.js',
            ],
            refresh: true,
        }),
    ],
});

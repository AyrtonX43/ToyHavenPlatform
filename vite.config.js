import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/css/seller.css', 'resources/js/app.js', 'resources/js/echo-conversation.js', 'resources/js/echo-auction.js'],
            refresh: true,
        }),
    ],
});

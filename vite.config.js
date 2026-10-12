import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';


export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.jsx', 'resources/css/app.css'],
            refresh: true,
        }),
    ],
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
        cors: { origin: ['http://localhost', 'http://localhost:8000', 'http://127.0.0.1:8000'] },
        allowedHosts: ['localhost', '127.0.0.1'],
    },
});

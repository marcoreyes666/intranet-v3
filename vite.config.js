import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
  server: {
    host: '0.0.0.0',        // escucha en todas las interfaces
    port: 5173,             // puerto por defecto de Vite
    strictPort: true,
    hmr: {
      host: '192.168.1.166', // <-- tu IP local
      port: 5173
    }
  },
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
  ],
})

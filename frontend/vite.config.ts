import { fileURLToPath, URL } from 'node:url';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

// In dev, proxy /api to the Go backend so the browser sees a single origin and
// the HTTP-only hkbp_session cookie behaves the same as in production (where the
// Go server serves the built SPA from STATIC_DIR on the same origin).
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: process.env.VITE_DEV_API_TARGET ?? 'http://localhost:8080',
        changeOrigin: true
      }
    }
  }
});

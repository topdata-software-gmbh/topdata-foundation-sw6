import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'src'),
    },
  },
  build: {
    outDir: '../../public/administration/js',
    rollupOptions: {
      input: {
        main: './src/main.ts',
      },
      output: {
        entryFileNames: 'topdata-foundation-sw6.js',
      },
    },
  },
  server: {
    hmr: {
      host: 'localhost',
    },
  },
});
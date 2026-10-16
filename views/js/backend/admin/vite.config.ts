import vue from '@vitejs/plugin-vue';
import {createViteConfig} from '@myparcel-prestashop/vite-config';

/**
 * @see https://vitejs.dev/config/
 */
export default createViteConfig({
  plugins: [vue()],

  build: {
    lib: {
      name: 'MyParcelPrestaShopAdmin',
      fileName: 'index',
      // PsBackendScriptService loads dist/style.css
      cssFileName: 'style',
      entry: 'src/main.ts',
      formats: ['iife'],
    },
    rollupOptions: {
      external: ['vue', 'vitest'],
      output: {
        globals: {
          vue: 'Vue',
        },
      },
    },
  },

  define: {
    'process.env': {},
  },

  test: {
    environment: 'happy-dom',
  },
});

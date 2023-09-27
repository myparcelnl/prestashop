import vue from '@vitejs/plugin-vue';
import {createViteConfig} from '@myparcel-prestashop/vite-config';

/**
 * @see https://vitejs.dev/config/
 */
export default createViteConfig({
  build: {
    lib: {
      entry: 'src/main.ts',
      fileName: 'index',
      formats: ['iife'],
      name: 'MyParcelAdmin',
    },
    rollupOptions: {
      external: ['vue'],
      output: {
        globals: {
          vue: 'Vue',
          pinia: 'Pinia',
          '@tanstack/vue-query': 'VueQuery',
        },
      },
    },
  },

  plugins: [vue()],
});

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
      entry: 'src/main.ts',
      formats: ['iife'],
    },
    rollupOptions: {
      external: ['vue', 'vue-demi'],
      output: {
        globals: {
          vue: 'Vue',
          'vue-demi': 'VueDemi',
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

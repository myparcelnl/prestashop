import {createViteConfig} from '@myparcel-prestashop/vite-config';

export default createViteConfig({
  build: {
    lib: {
      name: 'MyParcelPrestaShopCheckout',
      fileName: 'index',
      entry: 'src/main.ts',
      formats: ['iife'],
    },
  },
});

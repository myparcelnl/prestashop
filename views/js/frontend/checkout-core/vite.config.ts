import {createViteConfig} from '@myparcel-prestashop/vite-config';

export default createViteConfig({
  build: {
    lib: {
      name: 'MyParcelPrestaShopCheckoutCore',
      fileName: 'checkout-core',
      entry: 'src/main.ts',
      formats: ['iife'],
    },
  },
});

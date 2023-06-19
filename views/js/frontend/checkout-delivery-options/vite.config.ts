import {createViteConfig} from '@myparcel-prestashop/vite-config';

export default createViteConfig({
  build: {
    lib: {
      name: 'MyParcelPrestaShopCheckoutDeliveryOptions',
      fileName: 'checkout-delivery-options',
      entry: 'src/main.ts',
      formats: ['iife'],
    },
  },
});

import { initializeCheckoutDeliveryOptions, usePdkCheckout } from '@myparcel-pdk/checkout/src';

usePdkCheckout().onInitialize(() => {
  initializeCheckoutDeliveryOptions();
});

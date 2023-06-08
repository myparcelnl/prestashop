import { initializeCheckoutDeliveryOptions, usePdkCheckout } from '@myparcel-pdk/checkout';

usePdkCheckout().onInitialize(() => {
  initializeCheckoutDeliveryOptions();
});

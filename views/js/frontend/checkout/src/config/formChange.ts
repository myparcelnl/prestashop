import {
  type PdkCheckoutConfigInput,
  debounce,
  updateContext,
} from '@myparcel-dev/pdk-checkout';

export const formChange: PdkCheckoutConfigInput['formChange'] = (callback) => {
  const refresh = debounce(() => {
    callback();

    // The form values alone do not include cart quantities or variant weights.
    // Refresh after PrestaShop has saved the cart, once Delivery Options is initialized.
    if (window.MyParcelPdk?.stores?.deliveryOptions) {
      void updateContext().catch((error) => {
        console.warn(
          '[myparcelnl] Could not refresh the checkout context',
          error,
        );
      });
    }
  });

  window.prestashop.on('updatedDeliveryForm', refresh);
  window.prestashop.on('updatedCart', () => {
    if (document.querySelector('#js-delivery')) {
      refresh();
    }
  });
};

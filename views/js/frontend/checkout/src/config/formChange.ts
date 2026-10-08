import {
  type PdkCheckoutConfigInput,
  PdkField,
  debounce,
  updateContext,
  useCheckoutStore,
} from '@myparcel-dev/pdk-checkout';
import {getFormData} from './getFormData';

export const formChange: PdkCheckoutConfigInput['formChange'] = (callback) => {
  const refresh = debounce(async () => {
    // The checkout store still holds the carrier of the last form update. Other inputs of the delivery
    // form, such as the gift checkbox, fire the same event without a carrier change.
    const carrierChanged =
      getFormData()[PdkField.ShippingMethod] !==
      useCheckoutStore().state.form[PdkField.ShippingMethod];

    // PrestaShop has saved the carrier, which can change the package type and so the cart weight.
    // Refresh before the form update, so updateDeliveryOptions narrows the carrier settings of the new context.
    if (carrierChanged && window.MyParcelPdk?.stores?.deliveryOptions) {
      await updateContext().catch((error) => {
        console.warn(
          '[myparcelnl] Could not refresh the checkout context',
          error,
        );
      });
    }

    callback();
  });

  window.prestashop.on('updatedDeliveryForm', refresh);
};

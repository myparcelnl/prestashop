import { updateDeliveryOptionsConfig } from './updateDeliveryOptionsConfig';
import { initializeCheckoutDeliveryOptions, usePdkCheckout } from '@myparcel-pdk/checkout/src';
import { useCheckoutStore } from '@myparcel-pdk/checkout';

export const initializeMyParcelForm = (shippingAddress, billingAddress): void => {
  updateDeliveryOptionsConfig();
  usePdkCheckout().onInitialize(() => {
    const checkout = useCheckoutStore();

    checkout.set({
      form: {
        ['billing']: {
          ['address1']: billingAddress.fullStreet,
          ['country']: billingAddress.cc,
          ['postalCode']: billingAddress.postalCode,
          ['city']: billingAddress.city,
          ['number']: billingAddress.number,
        },
        ['shipping']: {
          ['address1']: shippingAddress.fullStreet,
          ['country']: shippingAddress.cc,
          ['postalCode']: shippingAddress.postalCode,
          ['city']: shippingAddress.city,
          ['number']: shippingAddress.number,
        },
      },
    });

    initializeCheckoutDeliveryOptions();
  });
};

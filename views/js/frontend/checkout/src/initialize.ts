import {createPdkCheckout, PdkField, AddressType, usePdkCheckout} from '@myparcel-pdk/checkout';
import {createFields} from './utils';
import {initializeDeliveryOptions} from './initializeDeliveryOptions';
import {
  formChange,
  hasDeliveryOptions,
  getForm,
  toggleField,
  doRequest,
  getFormData,
  pdkCheckoutInitialize,
} from './config';

const PREFIX_BILLING = `${AddressType.Billing}_`;
const PREFIX_SHIPPING = `${AddressType.Shipping}_`;
const createName = (name: string) => `[name="${name}"]`;

// eslint-disable-next-line max-lines-per-function
export const initialize = (): void => {
  createPdkCheckout({
    doRequest,

    selectors: {
      deliveryOptionsWrapper: '#mypa-delivery-options-wrapper',
    },

    fields: {
      [PdkField.AddressType]: '',
      [PdkField.ShippingMethod]: '',
      [AddressType.Billing]: createFields(PREFIX_BILLING, createName),
      [AddressType.Shipping]: createFields(PREFIX_SHIPPING, createName),
    },

    formData: {
      [PdkField.AddressType]: PdkField.AddressType,
      [PdkField.ShippingMethod]: PdkField.ShippingMethod,
      [AddressType.Billing]: createFields(PREFIX_BILLING),
      [AddressType.Shipping]: createFields(PREFIX_SHIPPING),
    },

    hasDeliveryOptions,
    getForm,
    getFormData,
    formChange,
    toggleField,
    initialize: pdkCheckoutInitialize,
    getAddressType: () => AddressType.Shipping,
    hasAddressType: () => true,
  });

  const pdkCheckout = usePdkCheckout();

  pdkCheckout.onInitialize(initializeDeliveryOptions);
};

import {createPdkCheckout, PdkField, AddressType, useUtil, Util, usePdkCheckout} from '@myparcel-pdk/checkout';
import {doRequest, getFormData, pageInitialize} from './pdk';
import {useShippingMethodData, createFields, getCurrentShippingMethod} from './functions';

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

    getAddressType(): AddressType {
      return AddressType.Shipping;
    },

    hasDeliveryOptions(shippingMethod) {
      const {shippingMethods} = useShippingMethodData();

      return shippingMethods.some((method) => method.value === shippingMethod);
    },

    getForm: () => {
      const getElement = useUtil(Util.GetElement);

      // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
      return getElement('#js-delivery')!;
    },

    getFormData,

    hasAddressType() {
      return true;
    },

    initialize: pageInitialize,

    formChange(callback) {
      window.prestashop.on('updatedDeliveryForm', callback);
    },

    toggleField(field: HTMLInputElement, show: boolean): void {
      const $field = jQuery(field);
      const $wrapper = $field.closest('.form-row');

      if (show) {
        $wrapper.show();
      } else {
        $wrapper.hide();
      }
    },
  });

  usePdkCheckout().onInitialize(() => {
    // @ts-expect-error todo
    window.MyParcelPdk.utils.getCurrentShippingMethod = getCurrentShippingMethod;
  });
};

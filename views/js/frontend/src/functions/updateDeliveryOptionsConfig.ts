import { createPdkCheckout } from '@myparcel-pdk/checkout/src';
import { createFields } from './createFields';
import { AddressType, PdkField, useUtil } from '@myparcel-pdk/checkout';

const PREFIX_BILLING = 'billing_';
const PREFIX_SHIPPING = 'shipping_';

const FIELD_SHIPPING_METHOD = 'shipping_method';

const createName = (name: string) => `[name="${name}"]`;
const createId = (name: string) => `#${name}`;

export const updateDeliveryOptionsConfig = (): void => {
  createPdkCheckout({
    async doRequest(endpoint) {
      const query = new URLSearchParams(endpoint.parameters).toString();

      const response = await window.fetch(`${endpoint.baseUrl}/${endpoint.path}?${query}`, {
        method: endpoint.method,
        body: endpoint.body,
      });

      if (response.ok) {
        return response;
      }
    },

    fields: {
      [PdkField.ShippingMethod]: createId('shipping_method'),
      [PdkField.ToggleAddressType]: '#ship-to-different-address-checkbox',
      [AddressType.Billing]: createFields(PREFIX_BILLING, createName),
      [AddressType.Shipping]: createFields(PREFIX_SHIPPING, createName),
    },

    formData: {
      [PdkField.ShippingMethod]: 'delivery_option_13',
      [PdkField.ToggleAddressType]: 'delivery_option_13',
      [AddressType.Billing]: createFields(PREFIX_BILLING),
      [AddressType.Shipping]: createFields(PREFIX_SHIPPING),
    },

    getForm: () => {
      const getElement = useUtil('getElement');

      // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
      return getElement('#js-delivery')!;
    },

    initialize: () => {
      return new Promise((resolve) => {
        jQuery(() => {
          resolve();
        });
      });
    },

    selectors: {
      deliveryOptionsWrapper: '#mypa-delivery-options-wrapper',
      hasAddressType: '.woocommerce-billing-fields__field-wrapper',
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
};

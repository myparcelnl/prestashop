import { createPdkCheckout, initializeCheckoutDeliveryOptions, usePdkCheckout } from '@myparcel-pdk/checkout/src';
import { getConfigStore } from './getConfigStore';
import { updateDeliveryOptions } from './updateDeliveryOptions';
import { createFields } from './createFields';
import { AddressType, PdkField, useUtil } from '@myparcel-pdk/checkout';

const PREFIX_BILLING = 'billing_';
const PREFIX_SHIPPING = 'shipping_';

const FIELD_SHIPPING_METHOD = 'shipping_method';

const createName = (name: string) => `[name="${name}"]`;
const createId = (name: string) => `#${name}`;

export const updateDeliveryOptionsConfig = (carrierName: string): void => {
  const configStore = getConfigStore();
  const hasCarrierConfig = configStore.hasOwnProperty(carrierName);

  createPdkCheckout({
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

  usePdkCheckout().onInitialize(() => {
    initializeCheckoutDeliveryOptions();
  });
  return;

  if (!hasCarrierConfig) {
    void $.ajax({
      url: `${window.myparcel_delivery_options_url}?carrier_id=${carrierName}`,
      dataType: 'json',
      async: false,
      success: function (data) {
        configStore[carrierName] = data;

        window.MyParcelConfig = configStore[carrierName]?.data ?? window.MyParcelConfig;
        updateDeliveryOptions();
      },
    });
  }

  window.MyParcelConfig = configStore[carrierName]?.data ?? window.MyParcelConfig;
};

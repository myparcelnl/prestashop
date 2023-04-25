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

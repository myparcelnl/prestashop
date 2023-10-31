import {type ShippingMethod} from '@myparcel-prestashop/frontend-checkout-core';
import {type DeliveryOptionsStoreState} from '@myparcel-pdk/checkout';
import {getDefaultDeliveryOptionsConfig} from './getDefaultDeliveryOptionsConfig';

export const toggleDeliveryOptions = (shippingMethod: ShippingMethod): Partial<DeliveryOptionsStoreState> => {
  if (!shippingMethod) {
    return {enabled: false};
  }

  const {carrier} = shippingMethod;

  const configuration = getDefaultDeliveryOptionsConfig();

  const carrierConfig = configuration.config?.carrierSettings?.[carrier];

  return {enabled: Boolean(carrierConfig)};
};
import {getDefaultDeliveryOptionsConfig} from './getDefaultDeliveryOptionsConfig';

// @ts-expect-error todo
export const toggleDeliveryOptions = (shippingMethod) => {
  if (!shippingMethod) {
    return {enabled: false};
  }

  const {carrier} = shippingMethod;

  const configuration = getDefaultDeliveryOptionsConfig();

  const carrierConfig = configuration.config?.carrierSettings?.[carrier];

  return {enabled: Boolean(carrierConfig)};
};

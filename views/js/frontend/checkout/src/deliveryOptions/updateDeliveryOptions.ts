import {type DeliveryOptionsStoreState} from '@myparcel-pdk/checkout';
import {getCurrentShippingMethod} from '../utils';
import {getDefaultDeliveryOptionsConfig} from './utils';

// eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
export const updateDeliveryOptions = (state: DeliveryOptionsStoreState) => {
  const currentShippingMethod = getCurrentShippingMethod();

  if (!currentShippingMethod) {
    return state.configuration.config;
  }

  const configuration = getDefaultDeliveryOptionsConfig();

  const {carrier} = currentShippingMethod;

  return {
    ...state.configuration.config,
    carrierSettings: {
      [carrier]: configuration.config?.carrierSettings?.[carrier] ?? {},
    },
  };
};

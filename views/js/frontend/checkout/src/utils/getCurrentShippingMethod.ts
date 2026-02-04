import {PdkField, useCheckoutStore} from '@myparcel-dev/pdk-checkout';
import {type ShippingMethod} from '../types';
import {useShippingMethodData} from './useShippingMethodData';

export const getCurrentShippingMethod = (): ShippingMethod | undefined => {
  const checkoutStore = useCheckoutStore();
  const currentShippingMethod = checkoutStore.state.form[PdkField.ShippingMethod];

  const {shippingMethods} = useShippingMethodData();

  return shippingMethods.find((method) => method.value === currentShippingMethod);
};

import {type PdkCheckoutConfigInput} from '@myparcel-pdk/checkout';
import {useShippingMethodData} from '../utils';

export const hasDeliveryOptions: PdkCheckoutConfigInput['hasDeliveryOptions'] = (shippingMethod) => {
  const {shippingMethods} = useShippingMethodData();

  return shippingMethods.some((method) => method.value === shippingMethod);
};

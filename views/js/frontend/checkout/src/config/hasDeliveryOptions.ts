import {type PdkCheckoutConfigInput, useSettings} from '@myparcel-dev/pdk-checkout';
import {useShippingMethodData} from '../utils';

/**
 * Check if delivery options should be shown for a given shipping method.
 *
 * Delivery options are only shown when:
 * - The cart allows delivery options (based on product settings), AND
 * - The shipping method is a MyParcel carrier
 */
export const hasDeliveryOptions: PdkCheckoutConfigInput['hasDeliveryOptions'] = (shippingMethod) => {
  const {shippingMethods} = useShippingMethodData();
  const settings = useSettings();

  const cartAllowsDeliveryOptions = settings.hasDeliveryOptions;
  const isMyParcelCarrier = shippingMethods.some((method) => method.value === shippingMethod);

  return cartAllowsDeliveryOptions && isMyParcelCarrier;
};

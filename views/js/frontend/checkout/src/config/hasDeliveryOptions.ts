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

  // Check if the cart allows delivery options (may be disabled by product settings).
  const cartAllowsDeliveryOptions = settings.hasDeliveryOptions;

  // Check if the shipping method is a MyParcel carrier.
  const isMyParcelCarrier = shippingMethods.some((method) => method.value === shippingMethod);

  return cartAllowsDeliveryOptions && isMyParcelCarrier;
};

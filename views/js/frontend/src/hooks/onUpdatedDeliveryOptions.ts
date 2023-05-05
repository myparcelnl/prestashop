import { getDeliveryOptionsFormHandle } from '../functions/jquery/getDeliveryOptionsFormHandle';
import { isOfType } from '@myparcel/ts-utils';
import { updateInput } from '../functions/updateInput';

/**
 * Initialize all listeners.
 */
export const onUpdatedDeliveryOptions = (event: Event): void => {
  getDeliveryOptionsFormHandle()?.slideDown();

  if (isOfType<CustomEvent>(event, 'detail')) {
    updateInput(event.detail);
  }
};

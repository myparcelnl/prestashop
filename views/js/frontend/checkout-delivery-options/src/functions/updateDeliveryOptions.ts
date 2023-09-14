import {toggleDeliveryOptions} from './toggleDeliveryOptions';
import {moveDeliveryOptionsForm} from './moveDeliveryOptionsForm';
import {getCurrentShippingMethod} from './getCurrentShippingMethod';

export const updateDeliveryOptions = (): void => {
  const currentShippingMethod = getCurrentShippingMethod();

  toggleDeliveryOptions(currentShippingMethod);

  if (!currentShippingMethod) {
    return;
  }

  const {row} = getCurrentShippingMethod();

  moveDeliveryOptionsForm(row);
};

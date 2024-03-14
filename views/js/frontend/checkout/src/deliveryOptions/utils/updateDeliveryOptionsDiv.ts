import {useDeliveryOptionsStore} from '@myparcel-pdk/checkout';
import {getCurrentShippingMethod} from '../../utils';
import {toggleDeliveryOptions} from './toggleDeliveryOptions';
import {moveDeliveryOptionsForm} from './moveDeliveryOptionsForm';

export const updateDeliveryOptionsDiv = (mode = 'move'): void => {
  const currentShippingMethod = getCurrentShippingMethod();

  if (!currentShippingMethod) {
    return;
  }

  const deliveryOptionsStore = useDeliveryOptionsStore();

  const newState = toggleDeliveryOptions(currentShippingMethod);

  void deliveryOptionsStore.set(newState);

  moveDeliveryOptionsForm(currentShippingMethod.row, mode);
};

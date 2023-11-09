import {useDeliveryOptionsStore} from '@myparcel-pdk/checkout';
import {getCurrentShippingMethod} from '../../utils';
import {toggleDeliveryOptions} from './toggleDeliveryOptions';
import {moveDeliveryOptionsForm} from './moveDeliveryOptionsForm';

export const updateDeliveryOptionsDiv = (): void => {
  const deliveryOptionsStore = useDeliveryOptionsStore();
  const currentShippingMethod = getCurrentShippingMethod();

  if (!currentShippingMethod) {
    return;
  }

  const newState = toggleDeliveryOptions(currentShippingMethod);

  void deliveryOptionsStore.set(newState);

  moveDeliveryOptionsForm(currentShippingMethod.row);
};

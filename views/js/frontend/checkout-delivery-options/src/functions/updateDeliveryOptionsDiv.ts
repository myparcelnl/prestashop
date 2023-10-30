import {useDeliveryOptionsStore} from '@myparcel-pdk/checkout';
import {toggleDeliveryOptions} from './toggleDeliveryOptions';
import {moveDeliveryOptionsForm} from './moveDeliveryOptionsForm';
import {getCurrentShippingMethod} from './getCurrentShippingMethod';

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

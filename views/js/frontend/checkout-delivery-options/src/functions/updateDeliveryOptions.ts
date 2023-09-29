import {useDeliveryOptionsStore} from '@myparcel-pdk/checkout';
import {toggleDeliveryOptions} from './toggleDeliveryOptions';
import {moveDeliveryOptionsForm} from './moveDeliveryOptionsForm';
import {getCurrentShippingMethod} from './getCurrentShippingMethod';

export const updateDeliveryOptions = (): void => {
  const deliveryOptionsStore = useDeliveryOptionsStore();
  const currentShippingMethod = getCurrentShippingMethod();

  if (!currentShippingMethod) {
    return;
  }

  const {row} = getCurrentShippingMethod();

  void deliveryOptionsStore.set(toggleDeliveryOptions(currentShippingMethod));

  moveDeliveryOptionsForm(row);
};

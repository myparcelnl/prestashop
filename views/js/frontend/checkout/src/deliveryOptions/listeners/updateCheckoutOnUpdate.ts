import {
  debounce,
  useDeliveryOptionsStore,
  type DeliveryOptionsStoreState,
  type StoreCallbackUpdate,
} from '@myparcel-dev/pdk-checkout';
import {objectIsEqual} from '@myparcel-dev/ts-utils';
import {getCurrentShippingMethod} from '../../utils';

const CHECKOUT_UPDATE_DELAY = 200;

export const onDeliveryOptionsOutputChange: StoreCallbackUpdate<DeliveryOptionsStoreState> = debounce(
  (newState, oldState) => {
    if (objectIsEqual(newState.output, oldState?.output)) {
      return;
    }

    const currentShippingMethod = getCurrentShippingMethod();

    if (!currentShippingMethod) {
      return;
    }

    const deliveryOptions = useDeliveryOptionsStore();

    // Update the hidden input.
    if (deliveryOptions.state.hiddenInput) {
      $(deliveryOptions.state.hiddenInput).trigger('change');
    }
  },
  CHECKOUT_UPDATE_DELAY,
);

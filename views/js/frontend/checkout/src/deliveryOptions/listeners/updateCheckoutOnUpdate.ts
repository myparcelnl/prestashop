import {
  debounce,
  useDeliveryOptionsStore,
  type DeliveryOptionsStoreState,
  type StoreCallbackUpdate,
} from '@myparcel-dev/pdk-checkout';
import {objectIsEqual} from '@myparcel-dev/ts-utils';
import {getCurrentShippingMethod, isDeliveryFormVisible} from '../../utils';

const CHECKOUT_UPDATE_DELAY = 200;

export const onDeliveryOptionsOutputChange: StoreCallbackUpdate<DeliveryOptionsStoreState> = debounce(
  (newState, oldState) => {
    // A delivery form change makes PrestaShop's core.js reload the checkout page, which then shows the delivery step.
    // Check here, not before the debounce: the customer can continue to the next step during the delay.
    if (objectIsEqual(newState.output, oldState?.output) || !isDeliveryFormVisible()) {
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

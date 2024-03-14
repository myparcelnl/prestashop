import {debounce, type DeliveryOptionsStoreState, type StoreCallbackUpdate} from '@myparcel-pdk/checkout';
import {objectIsEqual} from '@myparcel/ts-utils';
import {getCurrentShippingMethod} from '../../utils';

const CHECKOUT_UPDATE_DELAY = 200;

/**
 * Only do this once to avoid excessive animations.
 */
let done = false;

export const onDeliveryOptionsOutputChange: StoreCallbackUpdate<DeliveryOptionsStoreState> = debounce(
  (newState, oldState) => {
    if (objectIsEqual(newState.output, oldState?.output)) {
      return;
    }

    const currentShippingMethod = getCurrentShippingMethod();

    if (!currentShippingMethod) {
      return;
    }

    if (done) {
      return;
    }

    // Trigger a change event on the shipping method input to let PrestaShop fetch the new price.
    currentShippingMethod.input.trigger('change');
    done = true;
  },
  CHECKOUT_UPDATE_DELAY,
);

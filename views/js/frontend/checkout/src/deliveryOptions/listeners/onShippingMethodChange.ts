import {type StoreCallbackUpdate, type CheckoutStoreState} from '@myparcel-dev/pdk-checkout';
import {updateDeliveryOptionsDiv} from '../utils';

export const onShippingMethodChange: StoreCallbackUpdate<CheckoutStoreState> = (newState, oldState) => {
  if (newState.form.shippingMethod === oldState?.form.shippingMethod) {
    return;
  }

  updateDeliveryOptionsDiv();
};

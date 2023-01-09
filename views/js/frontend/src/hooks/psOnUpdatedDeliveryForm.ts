import {initializeMyParcelForm} from '../functions/initializeMyParcelForm';

export const psOnUpdatedDeliveryForm = (params: PsCallbackParameters): void => {
  initializeMyParcelForm(params.deliveryOption);
};

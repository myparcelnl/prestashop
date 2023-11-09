import {getDeliveryOptionsWrapper} from './getDeliveryOptionsWrapper';

export const showDeliveryOptionsForm = (): void => {
  const $wrapper = getDeliveryOptionsWrapper();

  if (!$wrapper) {
    return;
  }

  $wrapper.show();
};

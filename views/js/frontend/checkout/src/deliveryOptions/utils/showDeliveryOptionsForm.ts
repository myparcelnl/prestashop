import {getDeliveryOptionsWrapper} from './getDeliveryOptionsWrapper';

export const showDeliveryOptionsForm = (): void => {
  const $wrapper = getDeliveryOptionsWrapper();

  if (!$wrapper || $wrapper.is(':visible')) {
    return;
  }

  $wrapper.stop().show();
};

import {getDeliveryOptionsWrapper} from './getDeliveryOptionsWrapper';

export const showDeliveryOptionsForm = (): void => {
  const $wrapper = getDeliveryOptionsWrapper();

  if (!$wrapper || $wrapper.is(':visible')) {
    return;
  }

  // Some themes nest .carrier-extra-content inside .delivery-option, which breaks
  // the theme's own updatedDeliveryForm handler that is supposed to reveal it after a carrier switch.
  $wrapper.parents('.carrier-extra-content:hidden').show();

  $wrapper.stop().show();
};

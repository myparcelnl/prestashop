import {getDeliveryOptionsWrapper} from './getDeliveryOptionsWrapper';

export const moveDeliveryOptionsForm = ($destination: JQuery): void => {
  const $wrapper = getDeliveryOptionsWrapper();

  if (!$wrapper) {
    return;
  }

  $wrapper.hide();
  $destination.append($wrapper);
};

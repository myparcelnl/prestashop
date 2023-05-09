import { getDeliveryOptionsFormHandle } from './getDeliveryOptionsFormHandle';

export const getInput = (): JQuery => {
  let $input = $('#mypa-input');

  if (!$input.length) {
    $input = $('<input type="hidden" name="myparcelnl-checkout-data" />');

    const $wrapper = getDeliveryOptionsFormHandle();

    if ($wrapper) {
      $wrapper.append($input);
    }
  }

  return $input;
};

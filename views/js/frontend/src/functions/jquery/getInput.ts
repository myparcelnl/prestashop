import {getDeliveryOptionsFormHandle} from './getDeliveryOptionsFormHandle';

export const getInput = (): JQuery => {
  let $input = $('#mypa-input');

  if (!$input.length) {
    $input = $('<input type="hidden" id="mypa-input" name="myparcel-delivery-options" />');

    const $wrapper = getDeliveryOptionsFormHandle();

    if ($wrapper) {
      $wrapper.append($input);
    }
  }

  return $input;
};

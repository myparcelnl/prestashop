import {type MyParcelDeliveryOptions} from '@myparcel/delivery-options';
import {getInput} from './jquery/getInput';

export const updateInput = (data: MyParcelDeliveryOptions.Configuration): void => {
  const $input = getInput();
  const dataString = JSON.stringify(data);

  $input.val(dataString);

  const $checkoutDeliverStep = $('#checkout-delivery-step');

  const isOnDeliverStep = $checkoutDeliverStep.hasClass('js-current-step') || $checkoutDeliverStep.hasClass('-current');

  if (isOnDeliverStep) {
    $input.trigger('change');
  }
};

import {updateDeliveryOptionsConfig} from './updateDeliveryOptionsConfig';
import {updateDeliveryOptions} from './updateDeliveryOptions';
import {createOrMoveDeliveryOptionsForm} from './createOrMoveDeliveryOptionsForm';

/**
 * @param {jQuery} $deliveryOptionsRow
 */
export const initializeMyParcelForm = ($deliveryOptionsRow: JQuery): void => {
  const checkedInputs = $deliveryOptionsRow.find('input:checked') as JQuery<HTMLInputElement>;

  if (!$deliveryOptionsRow || !$deliveryOptionsRow.length || !checkedInputs.length) {
    return;
  }

  const carrierId = checkedInputs[0].value.split(',').join('');
  const $wrapper = $deliveryOptionsRow.next().find('.myparcel-delivery-options-wrapper');

  if (!$wrapper) {
    return;
  }

  createOrMoveDeliveryOptionsForm($wrapper);
  updateDeliveryOptionsConfig(carrierId);
  updateDeliveryOptions();
};

import {getDeliveryOptionsFormHandle} from './jquery/getDeliveryOptionsFormHandle';

/**
 * Move the form to the new delivery option, or create it if it doesn't exist yet.
 * @param {JQuery} $wrapper
 */
export const createOrMoveDeliveryOptionsForm = ($wrapper: JQuery): void => {
  const $form = getDeliveryOptionsFormHandle();

  if ($form) {
    $form.hide();
    $wrapper.append($form);
    return;
  }

  $wrapper.html(`
        <div id="myparcel-form-handle">
          <div class="loader"></div>
          <div id="myparcel-delivery-options"></div>
        </div>
      `);
};

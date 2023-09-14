export const moveDeliveryOptionsForm = ($destination: JQuery): void => {
  const $wrapper = jQuery('#mypa-delivery-options-wrapper');

  if (!$wrapper) {
    return;
  }

  $wrapper.hide();
  $destination.append($wrapper);
  $wrapper.show();
};

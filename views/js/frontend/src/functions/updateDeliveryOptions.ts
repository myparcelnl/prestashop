import {hasUnRenderedDeliveryOptions} from './hasUnRenderedDeliveryOptions';

export const updateDeliveryOptions = (): void => {
  if (hasUnRenderedDeliveryOptions()) {
    document.dispatchEvent(new Event('myparcel_render_delivery_options'));
  } else {
    document.dispatchEvent(new Event('myparcel_update_config'));
  }
};

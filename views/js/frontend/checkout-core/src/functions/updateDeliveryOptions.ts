import {hasUnRenderedDeliveryOptions} from './hasUnRenderedDeliveryOptions';

export const updateDeliveryOptions = (): void => {
  if (window.MyParcelPdk) {
    const deliveryOptionsStore = window.MyParcelPdk.stores.deliveryOptions;

    if (hasUnRenderedDeliveryOptions()) {
      document.dispatchEvent(new CustomEvent('myparcel_render_delivery_options', {detail: deliveryOptionsStore}));
    } else {
      document.dispatchEvent(new CustomEvent('myparcel_update_config', {detail: deliveryOptionsStore}));
    }
  }
};

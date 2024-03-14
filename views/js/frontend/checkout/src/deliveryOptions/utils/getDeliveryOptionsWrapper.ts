import {useConfig} from '@myparcel-pdk/checkout-common';

export const getDeliveryOptionsWrapper = (): JQuery => {
  const config = useConfig();

  return jQuery(config.selectors.deliveryOptionsWrapper);
};

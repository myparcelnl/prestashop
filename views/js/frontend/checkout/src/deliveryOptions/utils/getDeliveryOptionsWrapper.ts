import {useConfig} from '@myparcel-dev/pdk-checkout-common';

export const getDeliveryOptionsWrapper = (): JQuery => {
  const config = useConfig();

  return jQuery(config.selectors.deliveryOptionsWrapper);
};

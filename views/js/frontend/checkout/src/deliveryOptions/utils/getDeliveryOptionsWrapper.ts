import {useConfig} from '@myparcel-pdk/checkout-common';

export const getDeliveryOptionsWrapper = (): JQuery<HTMLElement> => {
  const config = useConfig();

  return jQuery(config.selectors.deliveryOptionsWrapper);
};

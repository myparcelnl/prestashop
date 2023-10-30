import {useDeliveryOptionsStore, type DeliveryOptionsConfiguration} from '@myparcel-pdk/checkout';

export const getDefaultDeliveryOptionsConfig = (): DeliveryOptionsConfiguration => {
  const deliveryOptionsStore = useDeliveryOptionsStore();

  return deliveryOptionsStore.state.configuration;
};

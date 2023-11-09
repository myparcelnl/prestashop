import {useDeliveryOptionsStore, type DeliveryOptionsConfiguration} from '@myparcel-pdk/checkout';

let configuration: DeliveryOptionsConfiguration | undefined;

export const getDefaultDeliveryOptionsConfig = (): DeliveryOptionsConfiguration => {
  if (!configuration) {
    const deliveryOptionsStore = useDeliveryOptionsStore();

    configuration = deliveryOptionsStore.state.configuration;
  }

  return configuration;
};

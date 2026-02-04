import {useDeliveryOptionsStore} from '@myparcel-dev/pdk-checkout';
import {type InputDeliveryOptionsConfiguration} from '@myparcel/delivery-options';

let configuration: InputDeliveryOptionsConfiguration | undefined;

export const getDefaultDeliveryOptionsConfig = (): InputDeliveryOptionsConfiguration => {
  if (!configuration) {
    const deliveryOptionsStore = useDeliveryOptionsStore();

    configuration = deliveryOptionsStore.state.configuration;
  }

  return configuration;
};

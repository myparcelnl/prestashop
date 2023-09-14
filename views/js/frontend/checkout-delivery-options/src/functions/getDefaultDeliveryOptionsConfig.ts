import {useDeliveryOptionsStore} from '@myparcel-pdk/checkout';
import {isOfType} from '@myparcel/ts-utils';
import {type MyParcelDeliveryOptions} from '@myparcel/delivery-options';

const deliveryOptionsConfig: MyParcelDeliveryOptions.Configuration = {} as MyParcelDeliveryOptions.Configuration;

export const getDefaultDeliveryOptionsConfig = (): MyParcelDeliveryOptions.Configuration => {
  if (!isOfType<MyParcelDeliveryOptions.Configuration>(deliveryOptionsConfig, 'config')) {
    const deliveryOptionsStore = useDeliveryOptionsStore();

    const {configuration} = deliveryOptionsStore.state;

    Object.assign(deliveryOptionsConfig, configuration);
  }

  return deliveryOptionsConfig;
};

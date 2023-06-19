import {type MyParcelDeliveryOptions} from '@myparcel/delivery-options';

type ConfigData = {
  data: MyParcelDeliveryOptions.Configuration;
};

type ConfigStore = Partial<Record<string, ConfigData>>;

const deliveryOptionsConfigStore: ConfigStore = {};

export const getConfigStore = (): ConfigStore => deliveryOptionsConfigStore;

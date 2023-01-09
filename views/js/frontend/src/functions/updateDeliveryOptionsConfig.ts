import {getConfigStore} from './getConfigStore';
import {updateDeliveryOptions} from './updateDeliveryOptions';

export const updateDeliveryOptionsConfig = (carrierName: string): void => {
  const configStore = getConfigStore();
  const hasCarrierConfig = configStore.hasOwnProperty(carrierName);

  if (!hasCarrierConfig) {
    void $.ajax({
      url: `${window.myparcel_delivery_options_url}?carrier_id=${carrierName}`,
      dataType: 'json',
      async: false,
      success: function (data) {
        configStore[carrierName] = data;

        window.MyParcelConfig = configStore[carrierName]?.data ?? window.MyParcelConfig;
        updateDeliveryOptions();
      },
    });
  }

  window.MyParcelConfig = configStore[carrierName]?.data ?? window.MyParcelConfig;
};

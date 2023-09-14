import {moveDeliveryOptionsForm} from './moveDeliveryOptionsForm';
import {getDefaultDeliveryOptionsConfig} from './getDefaultDeliveryOptionsConfig';

export const updateDeliveryOptions = () => {
  // @ts-expect-error todo
  const currentShippingMethod = window.MyParcelPdk.utils.getCurrentShippingMethod();

  if (!currentShippingMethod) {
    return;
  }

  const {carrier, row} = currentShippingMethod;

  // const deliveryOptionsStore = useDeliveryOptionsStore();
  const configuration = getDefaultDeliveryOptionsConfig();

  const carrierConfig = configuration.config?.carrierSettings?.[carrier];

  if (!carrierConfig) {
    // document.dispatchEvent(new CustomEvent('myparcel_hide_delivery_options'));
    return;
  }

  console.log('move delivery options form');
  moveDeliveryOptionsForm(row);

  // Update the config with only the carrier settings for the current carrier
  // const newConfig = {
  //   ...configuration,
  //   config: {
  //     ...configuration.config,
  //     carrierSettings: {
  //       [carrier]: carrierConfig,
  //     },
  //   },
  // };
  //
  // deliveryOptionsStore.set({
  //   configuration: newConfig,
  // });

  // document.dispatchEvent(new CustomEvent('myparcel_render_delivery_options', {detail: newConfig}));
};

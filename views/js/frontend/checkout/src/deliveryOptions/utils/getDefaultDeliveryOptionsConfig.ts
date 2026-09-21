import {
  useCheckoutStore,
  useDeliveryOptionsStore,
} from '@myparcel-dev/pdk-checkout';
import {type InputDeliveryOptionsConfiguration} from '@myparcel-dev/delivery-options';

export const getDefaultDeliveryOptionsConfig =
  (): InputDeliveryOptionsConfiguration => {
    const {configuration} = useDeliveryOptionsStore().state;
    const {context} = useCheckoutStore().state;

    // The widget configuration is narrowed to one carrier. The fresh checkout
    // context retains all carriers for switching shipping methods after cart changes.
    return {...configuration, config: context.config ?? configuration.config};
  };

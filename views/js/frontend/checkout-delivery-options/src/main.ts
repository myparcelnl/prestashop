import {
  initializeCheckoutDeliveryOptions,
  usePdkCheckout,
  useCheckoutStore,
  StoreListener,
} from '@myparcel-pdk/checkout';
import {getDefaultDeliveryOptionsConfig, updateDeliveryOptions} from './functions';

usePdkCheckout().onInitialize(() => {
  initializeCheckoutDeliveryOptions({
    updateDeliveryOptions(state) {
      const currentShippingMethod = window.MyParcelPdk.utils.getCurrentShippingMethod();

      if (!currentShippingMethod) {
        return state.configuration.config;
      }

      const configuration = getDefaultDeliveryOptionsConfig();

      const {carrier} = currentShippingMethod;

      console.log('carrier', carrier);

      return {
        ...state.configuration.config,
        carrierSettings: {
          [carrier]: configuration.config?.carrierSettings?.[carrier],
        },
      };
    },
  });

  const checkoutStore = useCheckoutStore();

  getDefaultDeliveryOptionsConfig();
  updateDeliveryOptions();

  checkoutStore.on(StoreListener.Update, (newState, oldState) => {
    if (newState.form.shippingMethod === oldState?.form.shippingMethod) {
      return;
    }

    console.log('shipping method changed', newState.form.shippingMethod);

    updateDeliveryOptions();
  });
});

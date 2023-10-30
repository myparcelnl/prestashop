import {
  usePdkCheckout,
  useCheckoutStore,
  initializeCheckoutDeliveryOptions,
  StoreListener,
} from '@myparcel-pdk/checkout';
import {getDefaultDeliveryOptionsConfig, updateDeliveryOptionsDiv} from './functions';

usePdkCheckout().onInitialize(() => {
  initializeCheckoutDeliveryOptions({
    updateDeliveryOptions(state) {
      // @ts-expect-error todo
      const currentShippingMethod = window.MyParcelPdk.utils.getCurrentShippingMethod();

      if (!currentShippingMethod) {
        return state.configuration.config;
      }

      const configuration = getDefaultDeliveryOptionsConfig();

      const {carrier} = currentShippingMethod;

      return {
        ...state.configuration.config,
        carrierSettings: {
          [carrier]: configuration.config?.carrierSettings?.[carrier] ?? {},
        },
      };
    },
  });

  const checkoutStore = useCheckoutStore();

  getDefaultDeliveryOptionsConfig();
  updateDeliveryOptionsDiv();

  checkoutStore.on(StoreListener.Update, (newState, oldState) => {
    if (newState.form.shippingMethod === oldState?.form.shippingMethod) {
      return;
    }

    updateDeliveryOptionsDiv();
  });
});

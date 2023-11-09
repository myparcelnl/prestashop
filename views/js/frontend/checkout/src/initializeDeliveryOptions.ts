import {
  initializeCheckoutDeliveryOptions,
  useCheckoutStore,
  StoreListener,
  useDeliveryOptionsStore,
  debounce,
} from '@myparcel-pdk/checkout';
import {
  updateDeliveryOptions,
  getDefaultDeliveryOptionsConfig,
  updateDeliveryOptionsDiv,
  onShippingMethodChange,
  onDeliveryOptionsOutputChange,
  showDeliveryOptionsForm,
} from './deliveryOptions';

export const initializeDeliveryOptions = (): void => {
  initializeCheckoutDeliveryOptions({updateDeliveryOptions});

  getDefaultDeliveryOptionsConfig();
  updateDeliveryOptionsDiv();

  const checkoutStore = useCheckoutStore();
  const deliveryOptionsStore = useDeliveryOptionsStore();

  checkoutStore.on(StoreListener.Update, onShippingMethodChange);
  deliveryOptionsStore.on(StoreListener.Update, onDeliveryOptionsOutputChange);

  window.prestashop.on(
    'updatedDeliveryForm',
    debounce((...args) => {
      console.warn('updatedDeliveryForm', args);

      showDeliveryOptionsForm();
    }, 300),
  );
};

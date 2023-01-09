import {getDeliveryOptionsRow} from './functions/jquery/getDeliveryOptionsRow';
import {initializeMyParcelForm} from './functions/initializeMyParcelForm';
import {onUpdatedDeliveryOptions} from './hooks/onUpdatedDeliveryOptions';
import {psOnChangedCheckoutStep} from './hooks/psOnChangedCheckoutStep';
import {psOnUpdatedDeliveryForm} from './hooks/psOnUpdatedDeliveryForm';

(() => {
  /**
   * Whether the listeners have been initialized.
   */
  let initialized = false;

  const initialize = () => {
    if (initialized) {
      return;
    }

    initialized = true;

    window.prestashop.on('updatedDeliveryForm', psOnUpdatedDeliveryForm);

    const deliveryOptionsRow = getDeliveryOptionsRow();

    if (!deliveryOptionsRow) {
      return;
    }

    initializeMyParcelForm(deliveryOptionsRow);

    document.addEventListener('myparcel_updated_delivery_options', onUpdatedDeliveryOptions);
  };

  document.addEventListener('DOMContentLoaded', (): void => {
    if (!document.querySelector('#checkout-delivery-step.js-current-step')) {
      window.prestashop.on('changedCheckoutStep', initialize);
      return;
    }

    initialize();
  });

  // Hack to keep prestashop from hiding the checkout when icons inside the delivery options are clicked.
  window.prestashop.on('changedCheckoutStep', psOnChangedCheckoutStep);
})();

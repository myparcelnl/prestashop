const isDeliveryStep = () => document.querySelector('#checkout-delivery-step.js-current-step');

export const pdkCheckoutInitialize = (): Promise<void> => {
  return new Promise((resolve) => {
    if (isDeliveryStep()) {
      resolve();
      return;
    }

    window.prestashop.on('changedCheckoutStep', () => {
      if (!isDeliveryStep()) {
        return;
      }

      resolve();
    });
  });
};

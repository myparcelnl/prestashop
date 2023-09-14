const isDeliveryStep = () => document.querySelector('#checkout-delivery-step.js-current-step');

export const pageInitialize = (): Promise<void> => {
  return new Promise((resolve) => {
    document.addEventListener('DOMContentLoaded', (): void => {
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
  });
};

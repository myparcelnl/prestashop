const isDeliveryStep = () => document.querySelector('#checkout-delivery-step.js-current-step');

export const pdkCheckoutInitialize = (): Promise<void> => {
  return new Promise((resolve) => {
    if (isDeliveryStep()) {
      resolve();
      return;
    }

    // PS 1.7/8: step changes emit 'changedCheckoutStep' via prestashop.on()
    window.prestashop.on('changedCheckoutStep', () => {
      if (isDeliveryStep()) {
        resolve();
      }
    });

    // PS 9 (Hummingbird): steps are Bootstrap Tabs, no changedCheckoutStep event is emitted.
    // Observe the delivery step element for the js-current-step class being added.
    const deliveryStep = document.querySelector('#checkout-delivery-step');

    if (deliveryStep) {
      const observer = new MutationObserver(() => {
        if (isDeliveryStep()) {
          observer.disconnect();
          resolve();
        }
      });

      observer.observe(deliveryStep, {attributes: true, attributeFilter: ['class']});
    }
  });
};

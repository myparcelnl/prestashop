import {DELIVERY_STEP_SELECTOR} from '../utils';

const isDeliveryStep = () => document.querySelector(`${DELIVERY_STEP_SELECTOR}.js-current-step`);

export const pdkCheckoutInitialize = (): Promise<void> => {
  return new Promise((resolve) => {
    if (isDeliveryStep()) {
      resolve();
      return;
    }

    const observers: MutationObserver[] = [];

    const cleanup = () => {
      observers.forEach((o) => o.disconnect());
    };

    const done = () => {
      cleanup();
      resolve();
    };

    // Deadlock prevention for themes without a delivery step. When the step exists but another step is current,
    // keep waiting: initializing there updates the delivery form, which makes core.js reload the checkout.
    setTimeout(() => {
      if (!document.querySelector(DELIVERY_STEP_SELECTOR)) {
        done();
      }
    }, 10_000);

    // PS 1.7/8: step changes emit 'changedCheckoutStep' via prestashop.on()
    window.prestashop.on('changedCheckoutStep', () => {
      if (isDeliveryStep()) {
        done();
      }
    });

    // PS 9 (Hummingbird): steps are Bootstrap Tabs, no changedCheckoutStep event is emitted.
    // Observe the delivery step element for the js-current-step class being added.
    const observeDeliveryStep = (element: Element) => {
      const observer = new MutationObserver(() => {
        if (isDeliveryStep()) {
          done();
        }
      });

      observers.push(observer);
      observer.observe(element, {attributes: true, attributeFilter: ['class']});
    };

    const deliveryStep = document.querySelector(DELIVERY_STEP_SELECTOR);

    if (deliveryStep) {
      observeDeliveryStep(deliveryStep);
      return;
    }

    // Element not yet in DOM — observe body for it to appear (e.g. non-Hummingbird PS9 themes)
    const bodyObserver = new MutationObserver(() => {
      const step = document.querySelector(DELIVERY_STEP_SELECTOR);

      if (step) {
        bodyObserver.disconnect();
        observeDeliveryStep(step);
      }
    });

    observers.push(bodyObserver);
    bodyObserver.observe(document.body, {childList: true, subtree: true});
  });
};

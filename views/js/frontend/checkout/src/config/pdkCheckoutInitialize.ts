import {isDeliveryFormVisible} from '../utils';

const POLL_INTERVAL = 250;

/** Start the delivery options when the customer can see the delivery form. */
export const pdkCheckoutInitialize = (): Promise<void> => {
  return new Promise((resolve) => {
    if (isDeliveryFormVisible()) {
      resolve();
      return;
    }

    // Polls on purpose: a stylesheet alone (:target, :has()) can show the form, and no observer reports that.
    const interval = setInterval(() => {
      if (isDeliveryFormVisible()) {
        clearInterval(interval);
        resolve();
      }
    }, POLL_INTERVAL);
  });
};

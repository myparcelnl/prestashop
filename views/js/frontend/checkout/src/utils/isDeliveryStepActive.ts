export const DELIVERY_STEP_SELECTOR = '#checkout-delivery-step';

/** True when the delivery step is the current checkout step, or when the theme has no delivery step. */
export const isDeliveryStepActive = (): boolean => {
  const step = document.querySelector(DELIVERY_STEP_SELECTOR);

  return !step || step.classList.contains('js-current-step');
};

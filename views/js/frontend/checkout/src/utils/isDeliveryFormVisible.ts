export const DELIVERY_FORM_SELECTOR = '#js-delivery';

/** Check whether the delivery form is in the page and the customer can see it. */
export const isDeliveryFormVisible = (): boolean => {
  const form = document.querySelector(DELIVERY_FORM_SELECTOR);

  // Same check as jQuery's :visible. It does not catch a form clipped by a parent with height 0 and overflow hidden.
  return Boolean(form && form.getClientRects().length > 0);
};

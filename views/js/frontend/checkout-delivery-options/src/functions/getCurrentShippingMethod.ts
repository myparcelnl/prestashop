export const getCurrentShippingMethod = () => {
  // @ts-expect-error todo
  return window.MyParcelPdk.utils.getCurrentShippingMethod();
};

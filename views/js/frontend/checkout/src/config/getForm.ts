import {type PdkCheckoutConfigInput, useUtil, Util} from '@myparcel-pdk/checkout';

export const getForm: PdkCheckoutConfigInput['getForm'] = () => {
  const getElement = useUtil(Util.GetElement);

  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  return getElement('#js-delivery')!;
};

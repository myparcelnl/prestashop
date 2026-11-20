import {type PdkCheckoutConfigInput, useUtil, Util} from '@myparcel-dev/pdk-checkout';
import {DELIVERY_FORM_SELECTOR} from '../utils';

export const getForm: PdkCheckoutConfigInput['getForm'] = () => {
  const getElement = useUtil(Util.GetElement);

  // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
  return getElement(DELIVERY_FORM_SELECTOR)!;
};

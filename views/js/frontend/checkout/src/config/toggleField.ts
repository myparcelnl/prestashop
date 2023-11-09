import {type PdkCheckoutConfigInput} from '@myparcel-pdk/checkout';

export const toggleField: PdkCheckoutConfigInput['toggleField'] = (field: HTMLInputElement, show: boolean): void => {
  const $field = jQuery(field);
  const $wrapper = $field.closest('.form-row');

  if (show) {
    $wrapper.show();
  } else {
    $wrapper.hide();
  }
};

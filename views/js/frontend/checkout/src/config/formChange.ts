import {type PdkCheckoutConfigInput, debounce} from '@myparcel-dev/pdk-checkout';

export const formChange: PdkCheckoutConfigInput['formChange'] = (callback) => {
  window.prestashop.on('updatedDeliveryForm', debounce(callback));
};

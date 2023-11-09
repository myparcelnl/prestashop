import {type PdkCheckoutConfigInput, debounce} from '@myparcel-pdk/checkout';

export const formChange: PdkCheckoutConfigInput['formChange'] = (callback) => {
  window.prestashop.on('updatedDeliveryForm', debounce(callback));
};

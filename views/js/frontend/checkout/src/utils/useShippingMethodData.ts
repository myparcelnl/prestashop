import {type StoredShippingMethodData} from '../types';
import {useCarrierData} from './useCarrierData';

const data: StoredShippingMethodData = {
  shippingMethodName: '',
  shippingMethods: [],
};

export const useShippingMethodData = (): StoredShippingMethodData => {
  if (data.shippingMethods.length) {
    return data;
  }

  const carrierData = useCarrierData();

  carrierData.forEach((carrier) => {
    // PS 9 (Hummingbird): radio input is inside .delivery-option__item ancestor
    // PS 1.7/8 (Classic): radio input is a sibling of the extra content container
    const $item = carrier.row.closest('.delivery-option__item, .delivery-option');
    const $checkbox = $item.length
      ? $item.find('input[type="radio"][name^="delivery_option"]')
      : carrier.row.parent().prev().find('input');

    data.shippingMethodName = $checkbox.attr('name') ?? '';

    const value = $checkbox.val()?.toString();

    data.shippingMethods.push({
      value: value ?? '?',
      carrier: carrier.carrier,
      row: carrier.row,
      input: $checkbox,
    });
  });

  return data;
};

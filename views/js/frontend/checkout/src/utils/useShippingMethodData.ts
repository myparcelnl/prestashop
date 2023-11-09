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
    const $checkbox = carrier.row.parent().prev().find('input');

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

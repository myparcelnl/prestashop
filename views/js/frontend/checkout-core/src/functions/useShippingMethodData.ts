import {type MyParcel} from '@myparcel/delivery-options';
import {useCarrierData} from './useCarrierData';

export interface ShippingMethod {
  carrier: MyParcel.CarrierIdentifier;
  input: JQuery;
  row: JQuery;
  value: string;
}

type StoredShippingMethodData = {
  shippingMethodName: string;
  shippingMethods: ShippingMethod[];
};

const data: StoredShippingMethodData = {
  shippingMethodName: '',
  shippingMethods: [],
};

const getShippingMethods = (): void => {
  if (data.shippingMethods.length) {
    return;
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
};

export const useShippingMethodData = (): StoredShippingMethodData => {
  getShippingMethods();

  return data;
};

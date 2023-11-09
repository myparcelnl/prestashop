import {type MyParcel} from '@myparcel/delivery-options';

export interface ShippingMethod {
  carrier: MyParcel.CarrierIdentifier;
  input: JQuery;
  row: JQuery;
  value: string;
}

export type StoredShippingMethodData = {
  shippingMethodName: string;
  shippingMethods: ShippingMethod[];
};

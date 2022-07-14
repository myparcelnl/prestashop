/* eslint-disable @typescript-eslint/naming-convention */

interface ShipmentLabel {
  ALLOW_DELIVERY_FORM: boolean;
  ALLOW_RETURN_FORM: boolean;
  barcode: string;
  date_add: string;
  date_upd: string;
  id_label: string;
  id_order: string;
  id_order_label: string;
  new_order_state: string;
  payment_url: string | null;
  status: string;
  track_link: string;

  /**
   * Additional field added by the frontend to be able to keep track of when labels were last refreshed.
   */
  refreshed_at?: string;
}

interface Consignment {
  canHaveAgeCheck: boolean;
  canHaveInsurance: boolean;
  canHaveLargeFormat: boolean;
  canHaveOnlyRecipient: boolean;
  canHaveReturn: boolean;
  canHaveSignature: boolean;
  insuranceOptions: number[];
}

interface DeliveryOptions {
  carrier: null | number;
  carrierName: null | string;
  date: null | string;
  deliveryType: null | string;
  isPickup: null | boolean;
  packageType: null | string;
  pickupLocation: null | never;
  shipmentOptions: null | ShipmentOptions;
}

interface ShipmentOptions {
  age_check?: null | boolean;
  insurance?: null | boolean;
  label_description?: null | boolean;
  large_format?: null | boolean;
  only_recipient?: null | boolean;
  return?: null | boolean;
  signature?: null | boolean;
}

interface LabelOptions {
  age_check: boolean;
  insurance: boolean;
  only_recipient: boolean;
  package_format: string;
  package_type: number;
  return: boolean;
  signature: boolean;
}

interface ExtraOptions {
  digitalStampWeight: string;
  labelAmount: number;
}

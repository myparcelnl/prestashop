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
  carrier: null | string;
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

type ModuleSettingsForm = '';

type ModuleSettingsFormItemConfigPropertyType = 'checkbox' | 'multi' | 'select' | 'submit' | 'switch' | 'text';

interface AddCarrierValue {
  name: string;
  value: string;
}

interface ModuleSettingsFormItem<C = ModuleSettingsFormItem> {
  type: 'tab' | 'accordion' | ModuleSettingsFormItemConfigPropertyType;
  label: string;
  name?: string;
  action?: [string, string];
  value?: string | number | boolean;
  description?: string;
  // options?: {}[];
  attributes?: Record<string, never>;
  children?: C[];
}

interface ModuleSettingsFormItemConfigProperty extends ModuleSettingsFormItem {
  type: ModuleSettingsFormItemConfigPropertyType;
  name?: string;
  value?: string | number | boolean;
}

interface Account {
  id: number;
  platform_id: number;
}

interface Shop {
  id: number;
  name: string;
}

interface Carrier {
  human: string;
  id: number;
  name: string;
}

interface CarrierOption {
  carrier: Carrier;
  enabled: boolean;
  label: string;
  optional: boolean;
}

interface DropOffPoint {
  box_number?: string;
  cc: string;
  city: string;
  location_code: string;
  location_name: string;
  number: number;
  number_suffix?: string;
  postal_code: string;
  region?: string;
  retail_network_id: string;
  state?: string;
  street: string;
}

interface CarrierConfiguration {
  carrier_id: number;
  default_drop_off_point: DropOffPoint;
  default_drop_off_point_identifier: string;
}

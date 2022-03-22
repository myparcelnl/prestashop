/* eslint-disable @typescript-eslint/naming-convention */

export enum ContextKey {
  RETURNS_FORM = 'returnsForm',
  SHIPMENT_LABELS = 'shipmentLabels',
  SHIPMENT_OPTIONS = 'shipmentOptions',
  SHIPPING_ADDRESS = 'shippingAddress',
  PRINT_OPTIONS = 'printOptions',
  ACCOUNT_SETTINGS = 'accountSettings',
  MODULE_SETTINGS_FORM = 'moduleSettingsForm',
  MODULE_SETTINGS_VALUES = 'moduleSettingsValues',
  ADD_CARRIER_FORM = 'addCarrierForm',
}

export type MyParcelContext<T> =
  T extends ContextKey.RETURNS_FORM ? Context<ReturnsFormContext> :
    T extends ContextKey.SHIPMENT_LABELS ? Context<ShipmentLabelsContext> :
      T extends ContextKey.SHIPMENT_OPTIONS ? Context<ShipmentOptionsContext> :
        T extends ContextKey.SHIPPING_ADDRESS ? Context<ShippingAddressContext> :
          T extends ContextKey.PRINT_OPTIONS ? Context<PrintOptionsContext> :
            T extends ContextKey.MODULE_SETTINGS_FORM ? Context<ModuleSettingsFormContext> :
              T extends ContextKey.MODULE_SETTINGS_VALUES ? Context<ModuleSettingsValuesContext> :
                T extends ContextKey.ACCOUNT_SETTINGS ? Context<AccountSettingsContext> :
                  T extends ContextKey.ADD_CARRIER_FORM ? Context<ModuleSettingsFormItem> :
                    never;

export interface BaseContext<Key = ContextKey> {
  id?: Key;
  orderId?: number | null;
}

export type Context<C = Record<never, never>> = C & BaseContext;

export type AnyContext = MyParcelContext<ContextKey>;

/**
 * @see \Gett\MyparcelBE\Module\Hooks\ShipmentOptionsRenderService::getContext
 */
export interface ShipmentOptionsContext {
  consignment: Consignment;
  deliveryOptions: DeliveryOptions;
  deliveryOptionsDateChanged: boolean;
  extraOptions: ExtraOptions;
  labelOptions: LabelOptions;
  options: Record<'digitalStampWeight' | 'packageFormat' | 'packageType', SelectOption[]>;
  orderId: number | null;
  orderWeight: number;
}

/**
 * @see \Gett\MyparcelBE\Module\Hooks\ShipmentOptionsRenderService::getContext
 */
export interface ShippingAddressContext {
  action: string;
  addressId: number;
  formattedAddress: string;
}

export interface ShipmentLabelsContext {
  labels: ShipmentLabel[];
}

export interface ReturnsFormContext {
  name: string;
  email: string;
}

export interface PrintOptionsContext {
  labelFormat: LabelFormat;
  labelOutput: LabelOutput;
  labelPosition: LabelPosition[];
  promptForLabelPosition: boolean;
}

export interface AccountSettingsContext {
  account: Account;
  shop: Shop;
  carrierConfigurations: CarrierConfiguration[];
  carrierOptions: CarrierOption[];
}

interface ModuleSettingsTab {
  label: string;
  name: string;
  children: ModuleSettingsFormItem[];
}

export type ModuleSettingsFormContext = ModuleSettingsTab[];

export type ModuleSettingsValuesContext = Record<string, string | boolean | string[]>;

/**
 * This can't be defined in a d.ts file because the enums need to be available at runtime.
 */
declare global {
  interface Window {
    MyParcelContext: {
      [K in ContextKey]: MyParcelContext<K>
    };
  }
}

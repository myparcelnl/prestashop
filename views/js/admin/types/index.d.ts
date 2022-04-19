type Variant = 'primary' | 'secondary' | 'info' | 'warning' | 'danger' | 'success' | 'dark' | 'light';

interface DropdownButtonItem {
  label: string;
  action: string | number;
  variant?: Variant;
  icon?: string;
}

interface SelectOption<Value = string | number> {
  label: string;
  value: Value;
}

/**
 * Define the custom properties added to window.
 */
declare interface Window {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  MyParcel: Record<string, (...args?: any) => Promise<any>>;
  MyParcelActions: {
    adminUrl: string;
    deliveryOptionsUrl: string;
    pathLabel: string;
    pathLoading: string;
    pathOrder: string;
  };
  MyParcelTranslations: Record<string, string>;
  MyParcelConfiguration: MyParcelConfiguration;
}

interface MyParcelConfiguration {
  currencySign: string;
  dateFormatFull: DateFormatKey;
  dateFormatLite: DateFormatKey;

  /**
   * The path from server root to the module. Used to have webpack resolve paths in @/publicPath.ts.
   *
   * @example <PrestaShop instance path>/modules/myparcelbe/
   */
  modulePathUri: string;
}

type DateFormat = 'full' | 'lite';

type DateFormatKey = 'dateFormatFull' | 'dateFormatLite';

interface AlertData {
  content: string;
  variant: Variant;
}

type LabelFormat = 'a4' | 'a6';
type LabelOutput = 'open' | 'download';
type LabelPosition = '1' | '2' | '3' | '4';

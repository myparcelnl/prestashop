import {type MyParcelDeliveryOptions} from '@myparcel/delivery-options';

declare global {
  type PsCallbackParameters = {deliveryOption: JQuery; event: Event};

  interface Window {
    MyParcelConfig: MyParcelDeliveryOptions.Configuration;
    // eslint-disable-next-line @typescript-eslint/naming-convention
    myparcel_delivery_options_url: string;
    prestashop: {
      on: (name: string, callback: (event: PsCallbackParameters) => void) => void;
    };
  }
}

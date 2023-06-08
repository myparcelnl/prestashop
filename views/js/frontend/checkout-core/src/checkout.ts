import { AddressField, AddressType, createPdkCheckout, PdkField, useUtil } from '@myparcel-pdk/checkout';
import { createFields } from './functions/createFields';

const PREFIX_BILLING = `${AddressType.Billing}_`;
const PREFIX_SHIPPING = `${AddressType.Shipping}_`;

const createName = (name: string) => `[name="${name}"]`;
const createId = (name: string) => `#${name}`;

(() => {
  let shippingMethodName = '';
  const allowedShippingMethods: string[] = [];

  // TODO: use carrier extra content to find out which rows are ours
  const shippingMethodRows = document.querySelectorAll('.myparcelnl-carrier-row');

  shippingMethodRows.forEach((row) => {
    const $row = jQuery(row);

    const checkbox = $row.parent().prev().find('input');

    shippingMethodName = checkbox.attr('name') ?? '';

    const value = checkbox.val()?.toString();

    if (value) {
      allowedShippingMethods.push(value);
    }
  });

  console.log({shippingMethodName, allowedShippingMethods});

  createPdkCheckout({
    async doRequest(endpoint) {
      const query = new URLSearchParams(endpoint.parameters).toString();

      const response = await window.fetch(`${endpoint.baseUrl}/${endpoint.path}?${query}`, {
        method: endpoint.method,
        body: endpoint.body,
      });

      if (response.ok) {
        return response;
      }
    },

    fields: {
      [PdkField.ShippingMethod]: createId('shipping_method'),
      [PdkField.ToggleAddressType]: '#ship-to-different-address-checkbox',
      [AddressType.Billing]: createFields(PREFIX_BILLING, createName),
      [AddressType.Shipping]: createFields(PREFIX_SHIPPING, createName),
    },

    formData: {
      [PdkField.ShippingMethod]: shippingMethodName,
      [PdkField.ToggleAddressType]: 'delivery_option_13',
      [AddressType.Billing]: createFields(PREFIX_BILLING),
      [AddressType.Shipping]: createFields(PREFIX_SHIPPING),
    },

    getForm: () => {
      const getElement = useUtil('getElement');

      // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
      return getElement('#js-delivery')!;
    },

    getFormData() {
      const form = document.querySelector<HTMLFormElement>('#js-delivery');
      const deliveryFormData = Object.fromEntries(new FormData(form));

      const addressField = document.querySelector('.myparcelnl-address');

      const shippingAddressJson = addressField?.getAttribute('data-shipping-address') ?? '{}';
      const billingAddressJson = addressField?.getAttribute('data-billing-address') ?? '{}';

      const billingAddress = JSON.parse(billingAddressJson);
      const shippingAddress = JSON.parse(shippingAddressJson);

      return {
        [PdkField.ShippingMethod]: deliveryFormData[shippingMethodName ?? ''] ?? '',
        [`${AddressType.Billing}_${AddressField.Address1}`]: billingAddress.fullStreet ?? '',
        [`${AddressType.Billing}_${AddressField.Country}`]: billingAddress.cc ?? '',
        [`${AddressType.Billing}_${AddressField.PostalCode}`]: billingAddress.postalCode ?? '',
        [`${AddressType.Billing}_${AddressField.City}`]: billingAddress.city ?? '',
        [`${AddressType.Billing}_${AddressField.Number}`]: billingAddress.number ?? '',
        [`${AddressType.Shipping}_${AddressField.Address1}`]: shippingAddress.fullStreet ?? '',
        [`${AddressType.Shipping}_${AddressField.Country}`]: shippingAddress.cc ?? '',
        [`${AddressType.Shipping}_${AddressField.PostalCode}`]: shippingAddress.postalCode ?? '',
        [`${AddressType.Shipping}_${AddressField.City}`]: shippingAddress.city ?? '',
        [`${AddressType.Shipping}_${AddressField.Number}`]: shippingAddress.number ?? '',
      };
    },

    hasAddressType(addressType: AddressType) {
      // TODO
      return true;
    },

    initialize: () => {
      return new Promise((resolve) => {
        document.addEventListener('DOMContentLoaded', (): void => {
          console.log('DOMContentLoaded');
          resolve();

          if (!document.querySelector('#checkout-delivery-step.js-current-step')) {
            window.prestashop.on('changedCheckoutStep', () => {
              console.log('changedCheckoutStep');
              resolve();
            });
          }
        });
      });
    },

    onFormChange(callback) {
      window.prestashop.on('updatedDeliveryForm', callback);
    },

    selectors: {
      deliveryOptionsWrapper: '#mypa-delivery-options-wrapper',
    },

    toggleField(field: HTMLInputElement, show: boolean): void {
      const $field = jQuery(field);
      const $wrapper = $field.closest('.form-row');

      if (show) {
        $wrapper.show();
      } else {
        $wrapper.hide();
      }
    },
  });

  // todo export missing stuff from pdk/checkout
  // const checkout = useCheckoutStore();
  //
  // checkout.on(StoreListener.Update, (newState, oldState) => {
  //   const fieldsEqual = useUtil(Util.FieldsEqual);
  //   console.log('Update', newState, oldState);
  //
  //   if (fieldsEqual(newState.settings, oldState.settings, 'allowedShippingMethods')) {
  //     return;
  //   }
  //
  //   newState.settings.allowedShippingMethods = allowedShippingMethods;
  // });
})();

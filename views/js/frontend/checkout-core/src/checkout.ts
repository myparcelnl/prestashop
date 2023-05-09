import {AddressField, AddressType, PdkField, createPdkCheckout, useUtil} from '@myparcel-pdk/checkout/src';
import {createFields} from './functions/createFields';

const PREFIX_BILLING = `${AddressType.Billing}_`;
const PREFIX_SHIPPING = `${AddressType.Shipping}_`;

const createName = (name: string) => `[name="${name}"]`;
const createId = (name: string) => `#${name}`;

createPdkCheckout({
  getFormData() {
    const addressField = document.querySelector('.myparcelnl-address');

    const shippingAddressJson = addressField?.getAttribute('shipping-address-data') ?? '{}';
    const billingAddressJson = addressField?.getAttribute('billing-address-data') ?? '{}';

    const billingAddress = JSON.parse(billingAddressJson);
    const shippingAddress = JSON.parse(shippingAddressJson);

    return {
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
    [PdkField.ShippingMethod]: 'delivery_option_13',
    [PdkField.ToggleAddressType]: 'delivery_option_13',
    [AddressType.Billing]: createFields(PREFIX_BILLING),
    [AddressType.Shipping]: createFields(PREFIX_SHIPPING),
  },

  getForm: () => {
    const getElement = useUtil('getElement');

    // eslint-disable-next-line @typescript-eslint/no-non-null-assertion
    return getElement('#js-delivery')!;
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

  onFormChange(callback) {
    window.prestashop.on('updatedDeliveryForm', callback);
  },

  hasAddressType(addressType: AddressType) {
    // TODO
    return true;
  },
});

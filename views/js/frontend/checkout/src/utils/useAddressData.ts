import {useUtil, Util} from '@myparcel-pdk/checkout';

interface Address {
  address1: string;
  cc: string;
  city: string;
  postalCode: string;
}

interface AddressData {
  billingAddress: Address;
  shippingAddress: Address;
}

const addressData: Partial<AddressData> = {};

export const useAddressData = (): AddressData => {
  if (useUtil(Util.IsOfType)<AddressData>(addressData, 'billingAddress')) {
    return addressData;
  }

  const dataField = document.querySelector('#myparcelnl-address-data');

  if (!dataField) {
    throw new Error('No address data found');
  }

  const shippingAddressJson = dataField.getAttribute('data-shipping-address') ?? '{}';
  const billingAddressJson = dataField.getAttribute('data-billing-address') ?? '{}';

  addressData.shippingAddress = JSON.parse(shippingAddressJson);
  addressData.billingAddress = JSON.parse(billingAddressJson);

  dataField.remove();

  return addressData as AddressData;
};

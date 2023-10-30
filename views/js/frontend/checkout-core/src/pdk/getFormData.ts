import {PdkField, AddressType, AddressField, type PdkCheckoutConfigInput} from '@myparcel-pdk/checkout';
import {useAddressData, useShippingMethodData} from '../functions';

export const getFormData: PdkCheckoutConfigInput['getFormData'] = () => {
  const {shippingMethodName} = useShippingMethodData();

  const form = document.querySelector<HTMLFormElement>('#js-delivery');
  const deliveryFormData = Object.fromEntries(new FormData(form!));

  const {billingAddress, shippingAddress} = useAddressData();

  return {
    [PdkField.AddressType]: AddressType.Shipping,
    [PdkField.ShippingMethod]: deliveryFormData[shippingMethodName ?? ''] ?? '',
    [`${AddressType.Billing}_${AddressField.Address1}`]: billingAddress.address1 ?? '',
    [`${AddressType.Billing}_${AddressField.Country}`]: billingAddress.cc ?? '',
    [`${AddressType.Billing}_${AddressField.PostalCode}`]: billingAddress.postalCode ?? '',
    [`${AddressType.Billing}_${AddressField.City}`]: billingAddress.city ?? '',
    [`${AddressType.Shipping}_${AddressField.Address1}`]: shippingAddress.address1 ?? '',
    [`${AddressType.Shipping}_${AddressField.Country}`]: shippingAddress.cc ?? '',
    [`${AddressType.Shipping}_${AddressField.PostalCode}`]: shippingAddress.postalCode ?? '',
    [`${AddressType.Shipping}_${AddressField.City}`]: shippingAddress.city ?? '',
  };
};

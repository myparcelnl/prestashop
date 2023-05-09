import {AddressField, AddressFields} from '@myparcel-pdk/checkout/src';

export const createFields = (
  prefix: string,
  callback: (string: string) => string = (string: string) => string,
): AddressFields =>
  Object.values(AddressField).reduce(
    (acc, value) => ({
      ...acc,
      [value]: callback(`${prefix}${value}`),
    }),
    {} as AddressFields,
  );

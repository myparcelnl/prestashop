import {AddressField, type AddressFields} from '@myparcel-dev/pdk-checkout';

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

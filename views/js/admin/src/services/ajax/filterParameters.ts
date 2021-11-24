import { isOfType } from '@/utils/type-guard/isOfType';

/**
 * Filter keys with falsy values out of an object.
 */
export function filterParameters<T extends RequestParameters>(object: T): Partial<Record<keyof T, string>> {
  return Object
    .entries(object)
    .reduce((acc, [key, value]) => {
      if (isOfType(value, 'toString')) {
        value = value.toString();
      }

      return key ? { ...acc, [key]: value } : acc;
    }, {});
}

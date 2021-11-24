/**
 * Type guard for checking if an object value is of a specific type by checking if a given key exists.
 */
export function isOfType<T>(value: any, property: keyof T): value is T {
  return value && undefined !== value[property];
}

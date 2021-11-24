/**
 * Type guard for checking if a value is a key of the given enum.
 */
export function isEnumValue<T extends Record<string, string>>(
  key: unknown,
  enumObject: T,
): key is T[keyof T] {
  return typeof key === 'string' && Object.values(enumObject).includes(key);
}

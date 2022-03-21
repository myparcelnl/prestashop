/**
 * Type guard which checks if given value is inside an array.
 */
export function isInArray<T, A extends T>(item: T, array: readonly A[]): item is A {
  return array.includes(item as A);
}

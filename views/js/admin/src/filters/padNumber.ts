const DEFAULT_PADDING = 2;

/**
 * Pad a number with leading zeroes.
 */
export function padNumber(number: number, amount = DEFAULT_PADDING): string {
  return number.toString().padStart(amount, '0');
}

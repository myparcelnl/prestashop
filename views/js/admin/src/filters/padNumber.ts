/**
 * Pad a number with leading zeroes.
 */
export function padNumber(number: number, amount = 2): string {
  return number.toString().padStart(amount, '0');
}

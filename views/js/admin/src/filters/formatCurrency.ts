let formatter: NumberFormatter;

/**
 * Formats a number into a localized currency string.
 */
export function formatCurrency(number: number): string {
  if (!formatter) {
    formatter = getCurrencyFormatter();
  }

  return formatter.format(number);
}

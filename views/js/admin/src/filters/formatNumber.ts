let formatter: NumberFormatter;

/**
 * Formats a number into a localized string.
 */
export function formatNumber(number: number): string {
  if (!formatter) {
    formatter = getNumberFormatter();
  }

  return formatter.format(number);
}

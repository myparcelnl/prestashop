interface NumberFormatter {
  format: (amount: number) => string;
}

/**
 * Formats numbers into currency strings.
 */
declare function getCurrencyFormatter(): NumberFormatter;

/**
 * Formats numbers into strings.
 */
declare function getNumberFormatter(): NumberFormatter;

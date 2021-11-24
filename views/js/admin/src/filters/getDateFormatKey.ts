/**
 * Get the correct key for a given DateFormat.
 */
export function getDateFormatKey(format: DateFormat): DateFormatKey {
  let key: keyof MyParcelConfiguration;

  switch (format) {
    case 'full':
      key = 'dateFormatFull';
      break;
    case 'lite':
      key = 'dateFormatLite';
      break;
    default:
      throw new Error(`Date format "${format}" not supported.`);
  }

  return key;
}

import { decodeHtmlEntities } from '@/utils/decodeHtmlEntities';

/**
 * Cached translations.
 *
 * @type {Object}
 */
const cache: Record<string, string> = {};

/**
 * Translate given value using the window object.
 *
 * @param {string} value
 * @returns {string}
 */
export function translate(value: string): string {
  if (!value) {
    return '';
  }

  let translated = value;

  if (window.MyParcelTranslations?.hasOwnProperty(value)) {
    if (!cache.hasOwnProperty(value)) {
      cache[value] = decodeHtmlEntities(window.MyParcelTranslations[value]);
    }

    translated = cache[value];
  } else {
    // eslint-disable-next-line no-console
    console.warn(`[MyParcel] Missing translation: ${value}`);
  }

  return translated;
}

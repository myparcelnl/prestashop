/**
 * Decodes any HTML entities in given string into their original strings.
 *
 * @param {string} string
 * @returns {string}
 */
export function decodeHtmlEntities(string: string): string {
  const textarea = document.createElement('textarea');
  textarea.innerHTML = string;
  return textarea.value;
}

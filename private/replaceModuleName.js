/* eslint-disable no-useless-escape */
// noinspection RegExpUnnecessaryNonCapturingGroup

/**
 * @param {string} input
 * @param {string} output
 *
 * @returns {string}
 */
function caseSensitiveMatcher(input, output) {
  return [...output]
    .map((outputChar, i) => {
      const inputChar = input.charAt(i);

      if (inputChar === inputChar.toUpperCase()) {
        return outputChar.toUpperCase();
      }

      return outputChar.toLowerCase();
    })
    .join('');
}

/**
 * Replaces all occurrences of a string with another string, case-sensitive
 *
 * @param {string} input
 * @param {string} search
 * @param {string} replace
 *
 * @returns {string}
 */
function replaceCaseSensitive(input, search, replace) {
  const res = new RegExp(search, 'gmi').exec(input);
  let output = input;

  if (res?.[0]) {
    const regExp = new RegExp(`${search}(?!\(?:Pdk|Sdk|DevTools))`, 'igm');

    output = input.replace(regExp, (search) => caseSensitiveMatcher(search, replace));
  }

  return output;
}

module.exports = {replaceCaseSensitive};

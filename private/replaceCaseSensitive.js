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
      } else {
        return outputChar.toLowerCase();
      }
    })
    .join('');
}

/**
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
    output = input.replace(new RegExp(search, 'igm'), (search) => caseSensitiveMatcher(search, replace));
  }

  return output;
}

module.exports = {replaceCaseSensitive};

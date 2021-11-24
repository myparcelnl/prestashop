const {spawn} = require('child_process');

/**
 * @param {string} command
 * @param {Object} options
 * @param {Function} callback
 */
function execute(command, options = {}, callback) {
  const split = command.split(' ');
  const args = split.slice(1, split.length);
  const process = spawn(split[0], args, {stdio: 'inherit', ...options});
  process.on('close', () => callback());
}

module.exports = {execute};

const {execute} = require('../execute');
const path = require('path');
const ci = require('ci-info');

/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createComposerTask(gulp, plugins, moduleName) {
  return (callback) => {
    const cwd = path.resolve(`dist/${moduleName}`);
    let command = `docker run --rm --volume ${cwd}:/app ghcr.io/myparcelnl/php-xd:7.4 composer install --no-dev`;

    if (ci.isCI) {
      command = 'composer install --no-dev';
    }

    execute(command, {cwd}, callback);
  };
}

module.exports = {createComposerTask};

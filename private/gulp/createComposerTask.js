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
    let command = `docker compose run --rm -it -v ${cwd}:/var/www/html web composer update --no-dev`;

    if (ci.isCI) {
      command = 'composer update --no-dev';
    }

    execute(command, {cwd}, callback);
  };
}

module.exports = {createComposerTask};

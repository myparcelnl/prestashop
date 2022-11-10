const {execute} = require('../execute');
const path = require('path');
const isCi = require('is-ci');

/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createComposerTask(gulp, plugins, moduleName) {
  return (callback) => {
    const cwd = path.resolve(`dist/${moduleName}`);
    const command = `composer update --no-dev`;

    execute(command, {cwd}, callback);
  };
}

module.exports = {createComposerTask};

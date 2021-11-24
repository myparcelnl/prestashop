const {adminAppDir} = require('./variables');
const {execute} = require('../execute');

/**
 * Install the vue app's dependencies.
 *
 * @returns {Function}
 */
function createVueInstallTask() {
  return (callback) => {
    execute('yarn --frozen-lockfile', {cwd: adminAppDir}, callback);
  };
}

module.exports = {createVueInstallTask};

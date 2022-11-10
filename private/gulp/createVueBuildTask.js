const {adminAppDir} = require('./variables');
const {execute} = require('../execute');

/**
 * Create a build of the vue app.
 *
 * @returns {Function}
 */
function createVueBuildTask() {
  return (callback) => {
    execute('yarn build-only', {cwd: adminAppDir}, callback);
  };
}

module.exports = {createVueBuildTask};

const {adminAppDir} = require('./variables');
const {execute} = require('../execute');

/**
 * Run the live dev server of the vue app.
 *
 * @returns {Function}
 */
function createVueDevTask() {
  return (callback) => {
    execute('yarn dev', {cwd: adminAppDir}, callback);
  };
}

module.exports = {createVueDevTask};

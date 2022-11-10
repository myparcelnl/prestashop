const {execute} = require('../execute');

/**
 * @returns {Function}
 */
function createJsBuildTask() {
  return (callback) => {
    const command = `yarn workspaces foreach --exclude . run build`;

    execute(command, {}, callback);
  };
}

module.exports = {createJsBuildTask};

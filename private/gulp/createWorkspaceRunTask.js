const {execute} = require('../execute');

/**
 * @param {string} task
 *
 * @returns {Function}
 */
function createWorkspaceRunTask(task) {
  return (callback) => {
    const command = `yarn workspaces foreach --parallel --exclude . run ${task}`;

    execute(command, {}, callback);
  };
}

module.exports = {createWorkspaceRunTask};

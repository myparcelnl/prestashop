const {modules} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @param {string} task
 */
function createTasksForAllModules(gulp, task) {
  gulp.task(task, gulp.parallel(...modules.map((moduleName) => `${task}:${moduleName}`)));
}

module.exports = {createTasksForAllModules};

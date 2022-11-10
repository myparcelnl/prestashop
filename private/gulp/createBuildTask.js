/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createBuildTask(gulp, plugins, moduleName) {
  return gulp.series(`transfer:${moduleName}`, `composer:update:${moduleName}`);
}

module.exports = {createBuildTask};

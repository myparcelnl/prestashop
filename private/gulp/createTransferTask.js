/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createTransferTask(gulp, plugins, moduleName) {
  return gulp.series(`copy:${moduleName}`, `transform:${moduleName}`);
}

module.exports = {createTransferTask};

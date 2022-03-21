/**
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createDevBuildTask(gulp) {
  return gulp.series(
    'clean',
    'js:copy',
    'copy:delivery-options',
  );
}

module.exports = {createDevBuildTask};

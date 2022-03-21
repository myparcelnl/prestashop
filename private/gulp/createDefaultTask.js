const {modules} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createDefaultTask(gulp) {
  return gulp.series(
    'clean',
    'js:build',
    'admin:build',
    'copy:delivery-options',
    gulp.parallel(
      ...modules.map((moduleName) => gulp.series(
        `build:${moduleName}`,
        `zip:${moduleName}`,
      )),
    ),
  );
}

module.exports = {createDefaultTask};

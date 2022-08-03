const {jsFiles} = require('./variables');

/**
 * Watch and refresh only the js parts.
 *
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createWatchJsTask(gulp) {
  const watch = () => {
    gulp.watch(jsFiles, null, gulp.series('build:js'));
  };

  return gulp.series('build:dev', gulp.parallel('admin:dev', watch));
}

module.exports = {createWatchJsTask};

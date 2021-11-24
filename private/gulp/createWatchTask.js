const {sourceFiles, jsFiles} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createWatchTask(gulp) {
  const watch = () => {
    gulp.watch(jsFiles, null, gulp.series('views:clean', 'admin:build', 'js:copy', 'copy:delivery-options', 'copy'));

    // When files are modified, just transfer them.
    gulp.watch(sourceFiles, {events: ['change']}, gulp.series('transfer'));

    // When files are added or deleted, transfer the files and run composer update.
    gulp.watch(sourceFiles, {events: ['add', 'unlink']}, gulp.series('build'));
  };

  return gulp.series(
    'build:dev',
    gulp.parallel(
      'admin:dev',
      watch,
    ),
  );
}

module.exports = {createWatchTask};

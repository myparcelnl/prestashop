const {sourceFiles, jsFiles} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createWatchTask(gulp) {
  const watch = () => {
    gulp.watch(jsFiles, null, gulp.series('build:js', 'copy'));

    // When files are modified, just transfer them.
    gulp.watch(sourceFiles, {events: ['change']}, gulp.series('transfer'));

    // When files are added or deleted, transfer the files and run composer install.
    gulp.watch(sourceFiles, {events: ['add', 'unlink']}, gulp.series('build'));
  };

  return gulp.series('build:dev', gulp.parallel('admin:dev', watch));
}

module.exports = {createWatchTask};

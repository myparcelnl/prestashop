const {jsFiles} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createJsCopyTask(gulp) {
  return () => gulp.src(jsFiles)
    .pipe(gulp.dest('views/dist/js'));
}

module.exports = {createJsCopyTask};

const {jsFiles} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createJsBuildTask(gulp, plugins) {
  const buffer = require('vinyl-buffer');
  const browserify = require('browserify');
  const babelify = require('babelify');

  return () => gulp.src(jsFiles, {read: false})
    .pipe(plugins.tap((file) => {
      file.contents = browserify(file.path)
        .transform(babelify)
        .bundle();
    }))
    .pipe(buffer())
    .pipe(plugins.sourcemaps.init())
    .pipe(plugins.uglify())
    .pipe(plugins.sourcemaps.write('.'))
    .pipe(gulp.dest('views/dist/js'));
}

module.exports = {createJsBuildTask};

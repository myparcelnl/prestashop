/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @returns {Function}
 */
function createViewsCleanTask(gulp, plugins) {
  return () => gulp.src('./views/dist/*', {allowEmpty: true, read: false})
    .pipe(plugins.clean({force: true}));
}

module.exports = {createViewsCleanTask};

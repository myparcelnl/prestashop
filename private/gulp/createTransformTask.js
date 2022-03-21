const path = require('path');
const {dist, MODULE_NAME_BE, sourceFiles} = require('./variables');
const {replaceCaseSensitive} = require('../replaceCaseSensitive');

/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createTransformTask(gulp, plugins, moduleName) {
  return () => gulp.src(sourceFiles, {base: '.'})
    .pipe(plugins.tap((file) => {
      if (!file.isDirectory()) {
        file.contents = Buffer.from(replaceCaseSensitive(file.contents.toString(), MODULE_NAME_BE, moduleName));
      }
    }))
    .pipe(plugins.rename((path) => {
      path.basename = replaceCaseSensitive(path.basename, MODULE_NAME_BE, moduleName);
    }))
    .pipe(gulp.dest(path.resolve(dist, moduleName)));
}

module.exports = {createTransformTask};

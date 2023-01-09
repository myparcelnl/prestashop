const {dist, sourceFiles, MODULE_NAME_NL} = require('./variables');
const gulp = require('gulp');
const path = require('path');
const rename = require('gulp-rename');
const {replaceCaseSensitive} = require('../replaceModuleName');
const tap = require('gulp-tap');

/**
 * @param {string} moduleName
 * @returns {Function}
 */
function createTransformTask(moduleName) {
  return () =>
    gulp
      .src(sourceFiles, {base: '.'})
      .pipe(
        tap((file) => {
          if (file.isDirectory()) {
            const filename = `${file.path}/index.php`;

            console.log(filename);
            return;
          }

          file.contents = Buffer.from(replaceCaseSensitive(file.contents.toString(), MODULE_NAME_NL, moduleName));
        }),
      )
      .pipe(
        rename((path) => {
          path.basename = replaceCaseSensitive(path.basename, MODULE_NAME_NL, moduleName);
        }),
      )
      .pipe(gulp.dest(path.resolve(dist, moduleName)));
}

module.exports = {createTransformTask};

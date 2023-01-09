const {dist, excludeFiles, moduleNameMap} = require('./variables');
const gulp = require('gulp');
const {version} = require('../../package.json');
const zip = require('gulp-zip');

/**
 * Collect all files and put them in a zip file.
 *
 * @param {string} moduleName
 *
 * @returns {Function}
 */
function createZipTask(moduleName) {
  return () =>
    gulp
      .src([`./dist/${moduleName}/**/*`, ...excludeFiles.map((filename) => `!./dist/${moduleName}/${filename}`)], {
        base: 'dist',
      })
      .pipe(zip(`${moduleNameMap[moduleName]}-${version}.zip`))
      .pipe(gulp.dest(dist));
}

module.exports = {createZipTask};

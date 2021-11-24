const {dist, excludeFiles, moduleNameMap} = require('./variables');
const {version} = require('../../package.json');

/**
 * Collect all files and put them in a zip file.
 *
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createZipTask(gulp, plugins, moduleName) {
  return () => gulp.src([
    `./dist/${moduleName}/**/*`,
    ...excludeFiles.map((filename) => `!./dist/${moduleName}/${filename}`),
  ], {base: 'dist'})
    .pipe(plugins.zip(`${moduleNameMap[moduleName]}-${version}.zip`))
    .pipe(gulp.dest(dist));
}

module.exports = {createZipTask};

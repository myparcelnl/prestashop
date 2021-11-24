const path = require('path');
const {dist, copyFiles} = require('./variables');

/**
 * @param {import('gulp').Gulp} gulp
 * @param {Object} plugins
 * @param {string} moduleName
 * @returns {Function}
 */
function createCopyTask(gulp, plugins, moduleName) {
  return () => gulp.src(copyFiles, {base: '.'})
    .pipe(gulp.dest(path.resolve(dist, moduleName)));
}

module.exports = {createCopyTask};

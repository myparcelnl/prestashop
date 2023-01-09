const gulp = require('gulp');
const path = require('path');
const {dist, copyFiles} = require('./variables');

/**
 * @param {string} moduleName
 * @returns {Function}
 */
function createCopyTask(moduleName) {
  return () => gulp.src(copyFiles, {base: '.'}).pipe(gulp.dest(path.resolve(dist, moduleName)));
}

module.exports = {createCopyTask};

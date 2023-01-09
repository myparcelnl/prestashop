const gulp = require('gulp');
const clean = require('gulp-clean');

/**
 * @param {string[]} globs
 * @returns {Function}
 */
function createCleanTask(globs) {
  return () => gulp.src(globs, {read: false}).pipe(clean({force: true}));
}

module.exports = {createCleanTask};

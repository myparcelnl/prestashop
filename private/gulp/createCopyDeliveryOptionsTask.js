/**
 * @param {import('gulp').Gulp} gulp
 * @returns {Function}
 */
function createCopyDeliveryOptionsTask(gulp) {
  return () => gulp.src([
    require.resolve('@myparcel/delivery-options/dist/myparcel.js'),
    require.resolve('@myparcel/delivery-options/dist/myparcel.lib.js'),
  ])
    .pipe(gulp.dest('views/dist/js/external/'));
}

module.exports = {createCopyDeliveryOptionsTask};

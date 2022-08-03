const {createCleanTask} = require('./private/gulp/createCleanTask');
const {createCopyTask} = require('./private/gulp/createCopyTask');
const {createWorkspaceRunTask} = require('./private/gulp/createWorkspaceRunTask');
const {createTransformTask} = require('./private/gulp/createTransformTask');
const {createZipTask} = require('./private/gulp/createZipTask');
const gulp = require('gulp');
const {modules} = require('./private/gulp/variables');

/**
 * Clean the /dist folder.
 */
gulp.task('clean', createCleanTask('dist/*'));

gulp.task('build:js', createWorkspaceRunTask('build'));
gulp.task('build:js:dev', createWorkspaceRunTask('build:dev'));

modules.forEach((moduleName) => {
  gulp.task(`copy:${moduleName}`, createCopyTask(moduleName));
  gulp.task(`copy:js:${moduleName}`, createCopyTask(moduleName));
  gulp.task(`transform:${moduleName}`, createTransformTask(moduleName));
  gulp.task(
    `build:${moduleName}`,
    gulp.series(`copy:${moduleName}`, `copy:js:${moduleName}`, `transform:${moduleName}`),
  );
  gulp.task(`zip:${moduleName}`, createZipTask(moduleName));
});

['build', 'copy', 'transform', 'zip'].forEach((task) => {
  gulp.task(task, gulp.parallel(...modules.map((moduleName) => `${task}:${moduleName}`)));
});

const defaultTask = gulp.series(
  'clean',
  'build:js',
  gulp.parallel(...modules.map((moduleName) => gulp.series(`build:${moduleName}`, `zip:${moduleName}`))),
);

gulp.task('build', defaultTask);

gulp.task(
  'build:dev',
  gulp.series(
    'clean',
    'build:js:dev',
    gulp.parallel(...modules.map((moduleName) => gulp.series(`build:${moduleName}`, `zip:${moduleName}`))),
  ),
);

exports.default = defaultTask;

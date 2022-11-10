const {createBuildTask} = require('./private/gulp/createBuildTask');
const {createCleanTask} = require('./private/gulp/createCleanTask');
const {createCopyTask} = require('./private/gulp/createCopyTask');
const {createDefaultTask} = require('./private/gulp/createDefaultTask');
const {createTasksForAllModules} = require('./private/gulp/createTasksForAllModules');
const {createTransferTask} = require('./private/gulp/createTransferTask');
const {createTransformTask} = require('./private/gulp/createTransformTask');
const {createViewsCleanTask} = require('./private/gulp/createViewsCleanTask');
const {createZipTask} = require('./private/gulp/createZipTask');
const gulp = require('gulp');
const {modules} = require('./private/gulp/variables');
const {createJsBuildTask} = require('./private/gulp/createJsBuildTask');
const plugins = require('gulp-load-plugins')();

/**
 * Clean the /dist folder.
 */
gulp.task('clean', createCleanTask(gulp, plugins));

/**
 * Clean the /views/dist folder.
 */
gulp.task('views:clean', createViewsCleanTask(gulp, plugins));

gulp.task('js:build', createJsBuildTask());

modules.forEach((moduleName) => {
  gulp.task(`copy:${moduleName}`, createCopyTask(gulp, plugins, moduleName));
  gulp.task(`transform:${moduleName}`, createTransformTask(gulp, plugins, moduleName));
  gulp.task(`transfer:${moduleName}`, createTransferTask(gulp, plugins, moduleName));
  // gulp.task(`build:${moduleName}`, createBuildTask(gulp, plugins, moduleName));
  gulp.task(`zip:${moduleName}`, createZipTask(gulp, plugins, moduleName));
});

// createTasksForAllModules(gulp, 'build');
createTasksForAllModules(gulp, 'transform');
createTasksForAllModules(gulp, 'copy');
createTasksForAllModules(gulp, 'transfer');
createTasksForAllModules(gulp, 'zip');

const defaultTask = createDefaultTask(gulp);

gulp.task('build', defaultTask);
// gulp.task('build:dev', createDevBuildTask(gulp));

exports.default = defaultTask;

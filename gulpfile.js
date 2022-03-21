const {createBuildTask} = require('./private/gulp/createBuildTask');
const {createCleanTask} = require('./private/gulp/createCleanTask');
const {createComposerTask} = require('./private/gulp/createComposerTask');
const {createCopyDeliveryOptionsTask} = require('./private/gulp/createCopyDeliveryOptionsTask');
const {createCopyTask} = require('./private/gulp/createCopyTask');
const {createDefaultTask} = require('./private/gulp/createDefaultTask');
const {createDevBuildTask} = require('./private/gulp/createDevBuildTask');
const {createJsBuildTask} = require('./private/gulp/createJsBuildTask');
const {createViewsCleanTask} = require('./private/gulp/createViewsCleanTask');
const {createJsCopyTask} = require('./private/gulp/createJsCopyTask');
const {createTasksForAllModules} = require('./private/gulp/createTasksForAllModules');
const {createTransferTask} = require('./private/gulp/createTransferTask');
const {createTransformTask} = require('./private/gulp/createTransformTask');
const {createVueBuildTask} = require('./private/gulp/createVueBuildTask');
const {createVueDevTask} = require('./private/gulp/createVueDevTask');
const {createVueInstallTask} = require('./private/gulp/createVueInstallTask');
const {createWatchJsTask} = require('./private/gulp/createWatchJsTask');
const {createWatchTask} = require('./private/gulp/createWatchTask');
const {createZipTask} = require('./private/gulp/createZipTask');
const gulp = require('gulp');
const {modules} = require('./private/gulp/variables');
const plugins = require('gulp-load-plugins')();

/**
 * Run babel on the javascript files.
 */
gulp.task('js:build', createJsBuildTask(gulp, plugins));

/**
 * Copy the js to dist without doing any processing on it.
 */
gulp.task('js:copy', createJsCopyTask(gulp));

/**
 * Copy delivery options into module.
 */
gulp.task('copy:delivery-options', createCopyDeliveryOptionsTask(gulp));

/**
 * Clean the /dist folder.
 */
gulp.task('clean', createCleanTask(gulp, plugins));

/**
 * Clean the /views/dist folder.
 */
gulp.task('views:clean', createViewsCleanTask(gulp, plugins));

/**
 * Admin vue app tasks.
 */
gulp.task('admin:install', createVueInstallTask());
gulp.task('admin:build', createVueBuildTask());
gulp.task('admin:dev', createVueDevTask());

modules.forEach((moduleName) => {
  gulp.task(`copy:${moduleName}`, createCopyTask(gulp, plugins, moduleName));
  gulp.task(`transform:${moduleName}`, createTransformTask(gulp, plugins, moduleName));
  gulp.task(`transfer:${moduleName}`, createTransferTask(gulp, plugins, moduleName));
  gulp.task(`composer:update:${moduleName}`, createComposerTask(gulp, plugins, moduleName));
  gulp.task(`build:${moduleName}`, createBuildTask(gulp, plugins, moduleName));
  gulp.task(`zip:${moduleName}`, createZipTask(gulp, plugins, moduleName));
});

createTasksForAllModules(gulp, 'build');
createTasksForAllModules(gulp, 'transform');
createTasksForAllModules(gulp, 'copy');
createTasksForAllModules(gulp, 'transfer');
createTasksForAllModules(gulp, 'composer:update');
createTasksForAllModules(gulp, 'zip');

const defaultTask = createDefaultTask(gulp);

gulp.task('build', defaultTask);
gulp.task('build:dev', createDevBuildTask(gulp));
gulp.task('watch', createWatchTask(gulp));
gulp.task('watch:js', createWatchJsTask(gulp));

exports.default = defaultTask;

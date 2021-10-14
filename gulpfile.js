const babelify = require('babelify');
const browserify = require('browserify');
const buffer = require('vinyl-buffer');
const clean = require('gulp-clean');
const gulp = require('gulp');
const rename = require('gulp-rename');
const {replaceCaseSensitive} = require('./private/replaceCaseSensitive');
const sourcemaps = require('gulp-sourcemaps');
const tap = require('gulp-tap');
const uglify = require('gulp-uglify');
const zip = require('gulp-zip');
const {exec} = require('child_process');

const MODULE_NAME_NL = 'myparcelnl';
const MODULE_NAME_BE = 'myparcelbe';

const modules = [MODULE_NAME_BE, MODULE_NAME_NL];

/**
 * Files where module name should be transformed in filenames and contents.
 *
 * @type {string[]}
 */
const sourceFiles = [
  './controllers/**/*',
  './mails/**/*',
  './src/**/*',
  './upgrade/**/*',
  './views/**/*',
  '!./views/dist/**/*',
  'composer.json',
  'index.php',
  'myparcelbe.php',
];

/**
 * Files that should be copied without modifying contents or filenames.
 *
 * @type {string[]}
 */
const copyFiles = [
  './views/dist/**/*',
  'composer.lock',
  'logo.png',
  'package-lock.json',
  'package.json',
];

/**
 * Files that should be excluded from the final zip file.
 *
 * @type {string[]}
 */
const excludeFiles = [
  'composer.json',
  'composer.lock',
  'package-lock.json',
  'package.json',
];

/**
 * Callback for use with tasks using child_process.exec().
 *
 * @param {Function} callback
 * @param {ExecException} err
 * @param {string} stdout
 * @param {string} stderr
 */
function execCallback(callback, err, stdout, stderr) {
  if (stderr) {
    // eslint-disable-next-line no-console
    console.warn(stderr);
  }

  if (typeof callback === 'function') {
    callback(err);
  }
}

/**
 * @param {string} moduleName
 * @returns {Function}
 */
function createTransformTask(moduleName) {
  return () => gulp.src(sourceFiles, {base: '.'})
    .pipe(tap((file) => {
      if (!file.isDirectory()) {
        file.contents = Buffer.from(replaceCaseSensitive(file.contents.toString(), MODULE_NAME_BE, moduleName));
      }
    }))
    .pipe(rename((path) => {
      path.basename = replaceCaseSensitive(path.basename, MODULE_NAME_BE, moduleName);
    }))
    .pipe(gulp.dest(`dist/${moduleName}`));
}

/**
 * @param {string} moduleName
 * @returns {Function}
 */
function createCopyTask(moduleName) {
  return () => gulp.src(copyFiles, {base: '.'})
    .pipe(gulp.dest(`dist/${moduleName}`));
}

/**
 * @param {string} moduleName
 *
 * @returns {Function}
 */
function createTransferTask(moduleName) {
  return gulp.series(
    `copy:${moduleName}`,
    `transform:${moduleName}`,
  );
}

/**
 * @param {string} moduleName
 * @returns {Function}
 */
function createBuildTask(moduleName) {
  return gulp.series(
    `transfer:${moduleName}`,
    `composer:update:${moduleName}`,
  );
}

/**
 * Collect all files and put them in a zip file.
 *
 * @param {string} moduleName
 * @returns {Function}
 */
function createZipTask(moduleName) {
  return () => gulp.src([
    `./dist/${moduleName}/**/*`,
    ...excludeFiles.map((filename) => `!./dist/${moduleName}/${filename}`),
  ], {base: 'dist'})
    .pipe(zip(`${moduleName}.zip`))
    .pipe(gulp.dest('dist'));
}

/**
 * @param {string} task
 */
function createTasksForAllModules(task) {
  gulp.task(task, gulp.parallel(...modules.map((moduleName) => `${task}:${moduleName}`)));
}

/**
 * Run babel on the javascript files.
 */
gulp.task('js:build', () => gulp.src(['./views/*.js'], {read: false})
  .pipe(tap((file) => {
    file.contents = browserify(file.path)
      .transform(babelify)
      .bundle();
  }))
  .pipe(buffer())
  .pipe(sourcemaps.init())
  .pipe(uglify())
  .pipe(sourcemaps.write('.'))
  .pipe(gulp.dest('views/dist')));

/**
 * Copy the js to dist without doing any processing on it.
 */
gulp.task('js:copy', () => gulp.src('views/js/**/*.js')
  .pipe(gulp.dest('views/dist/js')));

/**
 *
 * @param {string} moduleName
 * @returns {Function}
 */
function createComposerTask(moduleName) {
  return (callback) => {
    exec(`cd dist/${moduleName} && composer update`, (...params) => execCallback(callback, ...params));
  };
}

modules.forEach((moduleName) => {
  gulp.task(`copy:${moduleName}`, createCopyTask(moduleName));
  gulp.task(`transform:${moduleName}`, createTransformTask(moduleName));
  gulp.task(`transfer:${moduleName}`, createTransferTask(moduleName));
  gulp.task(`composer:update:${moduleName}`, createComposerTask(moduleName));
  gulp.task(`build:${moduleName}`, createBuildTask(moduleName));
  gulp.task(`zip:${moduleName}`, createZipTask(moduleName));
});

createTasksForAllModules('build');
createTasksForAllModules('transform');
createTasksForAllModules('transfer');
createTasksForAllModules('composer:update');
createTasksForAllModules('zip');

/**
 * Copy delivery options into module.
 */
gulp.task('copy:delivery-options', () => gulp.src('./node_modules/@myparcel/delivery-options/dist/myparcel.js')
  .pipe(gulp.dest('views/dist/')));

/**
 * @param string moduleName
 */
function createCleanTask(moduleName) {
  return () => gulp.src(`dist/${moduleName}/*`,
    {
      read: false,
      base: `dist/${moduleName}`,
    })
    .pipe(clean({force: true}));
}

/**
 * Empty the dist folder.
 */
gulp.task('clean', gulp.parallel(
  () => gulp.src('dist/*.*', {read: false}).pipe(clean({force: true})),
  createCleanTask('myparcelnl'),
  createCleanTask('myparcelbe'),
));

/**
 * The default task.
 */
const build = gulp.series(
  'clean',
  'js:build',
  'copy:delivery-options',
  gulp.parallel(
    ...modules.map((moduleName) => gulp.series(
      `build:${moduleName}`,
      `zip:${moduleName}`,
    )),
  ),
);

gulp.task('build', build);

const watch = () => {
  gulp.watch(['views/js/**/*'], null, gulp.series('js:copy'));

  // When files are modified, just transfer them.
  gulp.watch(sourceFiles, {events: ['change']}, gulp.series('transfer'));

  // When files are added or deleted, transfer the files and run composer update.
  gulp.watch(sourceFiles, {events: ['add', 'unlink']}, gulp.series('build'));
};

gulp.task('watch', gulp.series(
  build,
  watch,
));

exports.default = build;

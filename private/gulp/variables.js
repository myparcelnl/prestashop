const path = require('path');

const MODULE_NAME_NL = 'myparcelnl';
const MODULE_NAME_BE = 'myparcelbe';
const modules = [MODULE_NAME_BE, MODULE_NAME_NL];
const moduleNameMap = {
  [MODULE_NAME_NL]: 'MyParcelNL',
  [MODULE_NAME_BE]: 'MyParcelBE',
};

const dist = path.resolve(__dirname, '..', '..', 'dist');

/**
 * Files where module name should be transformed in filenames and contents.
 *
 * @type {string[]}
 */
const sourceFiles = [
  'controllers/**/*',
  'mails/**/*',
  'src/**/*',
  'upgrade/**/*',
  'views/**/*',
  '!views/js/**/*',
  '!views/js',
  '!dist/**/*',
  'composer.json',
  'index.php',
  'myparcelbe.php',
];

/**
 * JS files to watch/build/copy.
 *
 * @type {string[]}
 */
const jsFiles = [
  'views/js/**/*.js',
  '!./views/js/admin/**/*',
  '!**/node_modules/**/*',
];

/**
 * Files that should be copied without modifying contents or filenames.
 *
 * @type {string[]}
 */
const copyFiles = [
  'views/dist/**/*',
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
  'composer.lock',
  'package-lock.json',
  'package.json',
];

const adminAppDir = 'views/js/admin';

module.exports = {
  MODULE_NAME_BE,
  MODULE_NAME_NL,
  copyFiles,
  dist,
  excludeFiles,
  jsFiles,
  moduleNameMap,
  modules,
  sourceFiles,
  adminAppDir,
};

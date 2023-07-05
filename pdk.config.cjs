const { PdkPlatformName } = require('@myparcel-pdk/app-builder');

/**
 * @type {import('@myparcel-pdk/app-builder').PdkBuilderConfig}
 */
module.exports = {
  name: 'prestashop',
  platforms: [PdkPlatformName.MyParcelNl, PdkPlatformName.MyParcelBe],
  source: [
    '!**/node_modules/**',
    'vendor/**/*',
    'views/js/**/lib/**/*',
    'config/**/*',
    'src/**/*',
    'CONTRIBUTING.md',
    'LICENSE.txt',
    'README.md',
    'composer.json',
    'myparcelnl.php',
  ],

  versionSource: [
    { path: 'package.json' },
    { path: 'composer.json' },
  ],
};

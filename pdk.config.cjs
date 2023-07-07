const {PdkPlatformName, defineConfig} = require('@myparcel-pdk/app-builder');

module.exports = defineConfig({
  name: 'prestashop',
  platformFolderName: `{{platform}}`,
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
  versionSource: [{path: 'package.json'}, {path: 'composer.json'}],
});

import {PdkPlatformName, defineConfig} from '@myparcel-pdk/app-builder';
import {downloadCarrierLogos} from './private/downloadCarrierLogos.js';
import {spawnSync} from 'node:child_process';

export default defineConfig({
  name: 'prestashop',
  platformFolderName: '{{platform}}',
  platforms: [PdkPlatformName.MyParcelNl, PdkPlatformName.MyParcelBe],
  source: [
    '!**/node_modules/**',
    // Php files will be copied after scoping
    'mails/**/*',
    'private/carrier-logos/**/*',
    'views/PrestaShop/**/*',
    'views/js/**/dist/**/*',
    'views/templates/**/*',
    'CONTRIBUTING.md',
    'LICENSE.txt',
    'README.md',
    'logo.png',
  ],
  versionSource: [{path: 'package.json'}, {path: 'composer.json'}],
  translations: {
    // eslint-disable-next-line no-magic-numbers
    additionalSheet: 279275153,
  },

  rootCommand: 'docker compose run --rm -T php',

  hooks: {
    /**
     * Download carrier logos and build the frontend.
     */
    async beforeCopy({context}) {
      await downloadCarrierLogos(context);

      const buffer = spawnSync('yarn', ['nx', 'run-many', '--target=build', '--output-style=stream'], {
        stdio: 'inherit',
      });

      if (buffer.error) {
        throw buffer.error;
      }
    },
  },

  additionalCommands: [
    {
      name: 'download-carrier-logos',
      description: 'Download carrier logos',
      action: downloadCarrierLogos,
    },
  ],
});

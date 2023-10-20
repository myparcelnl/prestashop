import {
  PdkPlatformName,
  defineConfig,
  executeCommand,
  executePromises,
  getPlatformDistPath,
} from '@myparcel-pdk/app-builder';
import {downloadCarrierLogos} from './private/downloadCarrierLogos.js';
import fs from 'fs';
import glob from 'fast-glob';
import path from 'path';

export default defineConfig({
  name: 'prestashop',
  platformFolderName: '{{platform}}',
  platforms: [PdkPlatformName.MyParcelNl, PdkPlatformName.MyParcelBe],
  source: [
    '!**/node_modules/**',
    // Exclude autoload.php to regenerate it with a new hash
    '!.cache/build/vendor/autoload.php',
    '.cache/build/composer.json',
    '.cache/build/config/**/*',
    '.cache/build/controllers/**/*',
    '.cache/build/myparcelnl.php',
    '.cache/build/src/**/*',
    '.cache/build/upgrade/**/*',
    '.cache/build/vendor/**/*',
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

  additionalCommands: [
    {
      name: 'download-carrier-logos',
      description: 'Download carrier logos',
      action: downloadCarrierLogos,
    },
  ],

  hooks: {
    /**
     * Prefix the vendor and source php files.
     */
    async beforeCopy(args) {
      const {debug} = args.context;

      debug('Prefixing build files...');

      if (fs.existsSync('.cache/build/composer.json')) {
        debug('Build files already exist, skipping prefixing.');
        return;
      }

      if (!args.dryRun) {
        await executeCommand(
          args.context,
          'php',
          [
            '-d memory_limit=-1',
            '.cache/php-scoper/vendor/bin/php-scoper',
            'add-prefix',
            '--output-dir=.cache/build',
            '--force',
            '--no-ansi',
            '--no-interaction',
          ],
          {stdio: 'inherit'},
        );
      }

      debug('Finished prefixing build files.');
    },

    async afterCopy(args) {
      const {config, env, debug} = args.context;

      debug('Copying scoped build files to root');

      await executePromises(
        args,
        config.platforms.map(async (platform) => {
          const platformDistPath = getPlatformDistPath({config, env, platform});

          const files = glob.sync('.cache/build/**/*', {cwd: platformDistPath});

          await Promise.all(
            files.map(async (file) => {
              const oldPath = `${platformDistPath}/${file}`;
              const newPath = oldPath.replace('.cache/build/', '');

              if (!args.dryRun) {
                await fs.promises.mkdir(path.dirname(newPath), {recursive: true});
                await fs.promises.rename(oldPath, newPath);
              }
            }),
          );

          if (!args.dryRun) {
            await fs.promises.rm(`${platformDistPath}/.cache`, {recursive: true});
          }
        }),
      );

      debug('Copied scoped build files to root.');
    },

    async afterTransform(args) {
      const {config, debug, env} = args.context;

      await Promise.all(
        config.platforms.map(async (platform) => {
          debug(`Dumping composer autoloader for platform ${platform}...`);

          const distPath = getPlatformDistPath({...args.context, platform});
          const relativeDistPath = path.relative(env.cwd, distPath);

          if (!args.dryRun) {
            await executeCommand(
              args.context,
              'composer',
              ['dump-autoload', `--working-dir=${relativeDistPath}`, '--classmap-authoritative'],
              {stdio: 'inherit'},
            );
          }
        }),
      );

      debug('Dumped composer autoloader for all platforms.');
    },
  },
});

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

const TMP_DIR = '.tmp';
const SCOPED_DIR = `${TMP_DIR}/scoped`;

const scopePhp = (context, dest, config = 'scoper.inc.php') => {
  const {debug, env} = context;
  const destDir = path.resolve(env.cwd, dest);

  if (!fs.existsSync(destDir)) {
    debug(`Creating directory ${dest}`);
    fs.mkdirSync(destDir, {recursive: true});
  }

  if (fs.readdirSync(destDir).length > 0) {
    debug(`Skipping scoping php files to ${dest} because it already exists.`);
    return;
  }

  return executeCommand(
    context,
    'php',
    [
      '-d memory_limit=-1',
      `${TMP_DIR}/php-scoper/vendor/bin/php-scoper`,
      'add-prefix',
      `--config=${config}`,
      `--output-dir=${dest}`,
      '--force',
      '--no-ansi',
      '--no-interaction',
    ],
    {stdio: 'inherit'},
  );
};

export default defineConfig({
  name: 'prestashop',
  platformFolderName: '{{platform}}',
  platforms: [PdkPlatformName.MyParcelNl, PdkPlatformName.MyParcelBe],
  source: [
    '!**/node_modules/**',
    // Exclude autoload.php to regenerate it with a new hash
    `!${SCOPED_DIR}/vendor/autoload.php`,
    `${SCOPED_DIR}/vendor/**/*`,
    `${SCOPED_DIR}/source/**/*`,
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
    async beforeCopy({context}) {
      const {debug} = context;

      debug('Scoping php files...');

      await scopePhp(context, `${SCOPED_DIR}/source`);
      await scopePhp(context, `${SCOPED_DIR}/vendor`, 'scoper.vendor.inc.php');

      debug('Scoped all php files.');
    },

    async afterCopy({context}) {
      const {config, debug, args, env} = context;

      debug('Copying scoped build files to root');

      await executePromises(
        args,
        config.platforms.map(async (platform) => {
          const platformDistPath = getPlatformDistPath({...context, platform});

          const files = glob.sync([`${SCOPED_DIR}/source/**/*`, `${SCOPED_DIR}/vendor/**/*`], {cwd: platformDistPath});

          await Promise.all(
            files.map(async (file) => {
              const oldPath = `${platformDistPath}/${file}`;
              const newPath = oldPath.replace(`${SCOPED_DIR}/source/`, '').replace(`${SCOPED_DIR}/`, '');

              if (!args.dryRun) {
                await fs.promises.mkdir(path.dirname(newPath), {recursive: true});
                await fs.promises.rename(oldPath, newPath);
              }
            }),
          );

          if (!args.dryRun) {
            await fs.promises.rm(`${platformDistPath}/${TMP_DIR}`, {recursive: true});
          }

          if (args.verbose >= 1) {
            debug(`Removed ${path.relative(env.cwd, `${platformDistPath}/${TMP_DIR}`)}`);
          }
        }),
      );

      debug('Copied scoped build files to root.');
    },

    async afterTransform({context}) {
      const {config, debug, env, args} = context;

      await Promise.all(
        config.platforms.map(async (platform) => {
          debug(`Dumping composer autoloader for platform ${platform}...`);

          const distPath = getPlatformDistPath({...context, platform});
          const relativeDistPath = path.relative(env.cwd, distPath);

          if (!args.dryRun) {
            await executeCommand(
              context,
              'composer',
              ['dump-autoload', `--working-dir=${relativeDistPath}`, '--classmap-authoritative'],
              args.verbose >= 1 ? {stdio: 'inherit'} : {},
            );
          }
        }),
      );

      debug('Dumped composer autoloader for all platforms.');
    },
  },
});

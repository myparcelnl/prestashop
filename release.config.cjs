/* eslint-disable no-template-curly-in-string */
const mainConfig = require('@myparcel/semantic-release-config');
const {addExecPlugin, addGitHubPlugin, addGitPlugin} = require('@myparcel/semantic-release-config/src/plugins');
const {
  addCommitAnalyzerPlugin,
  addGitHubActionsOutputPlugin,
  addReleaseNotesGeneratorPlugin,
  addChangelogPlugin,
} = require('@myparcel/semantic-release-config/src/plugins/index.js');
const {spawnSync} = require('child_process');
const path = require('path');

const branch = spawnSync('git', ['rev-parse', '--abbrev-ref', 'HEAD']).stdout.toString().trim();

module.exports = {
  ...mainConfig,
  extends: '@myparcel/semantic-release-config',
  branches: [
    {name: 'main'},
    {name: 'develop', prerelease: 'rc', channel: 'rc'},
    {name: 'beta', prerelease: 'beta', channel: 'beta'},
    {name: 'alpha', prerelease: 'alpha', channel: 'alpha'},
  ],
  plugins: [
    addCommitAnalyzerPlugin(),
    addGitHubActionsOutputPlugin(),
    addReleaseNotesGeneratorPlugin({header: path.resolve(__dirname, `private/semantic-release/header-${branch}.md`)}),
    addChangelogPlugin(),
    addExecPlugin({
      prepareCmd: `yarn pdk-builder release --root-command "${process.env.PDK_ROOT_COMMAND}" --version $\{nextRelease.version} -vvv && zip -r ./dist/myparcel-prestashop-$\{nextRelease.version}.zip -C dist .`,
    }),
    addGitHubPlugin({
      assets: [
        {
          path: './dist/myparcel-*.zip',
          label: 'Download MyParcel PrestaShop v${nextRelease.version}',
        }
      ],
    }),
    addGitPlugin(),
  ],
};

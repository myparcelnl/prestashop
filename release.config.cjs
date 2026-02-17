/* eslint-disable no-template-curly-in-string */
const mainConfig = require('@myparcel-dev/semantic-release-config');
const {
  addExecPlugin,
  addGitHubPlugin,
  addGitPlugin,
  addCommitAnalyzerPlugin,
  addGitHubActionsOutputPlugin,
  addReleaseNotesGeneratorPlugin,
  addChangelogPlugin,
} = require('@myparcel-dev/semantic-release-config/src/plugins');
const {spawnSync} = require('child_process');
const path = require('path');

const branch = spawnSync('git', ['rev-parse', '--abbrev-ref', 'HEAD']).stdout.toString().trim();

module.exports = {
  ...mainConfig,
  extends: '@myparcel-dev/semantic-release-config',
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
      prepareCmd: `yarn pdk-builder release --version $\{nextRelease.version} -v && mkdir -p ./artifacts && cd dist && zip -r ../artifacts/myparcel-prestashop-$\{nextRelease.version}.zip .`,
    }),
    addGitHubPlugin({
      assets: [
        {
          path: './artifacts/myparcel-*.zip',
          label: 'Download MyParcel PrestaShop v${nextRelease.version}',
        }
      ],
    }),
    addGitPlugin(),
  ],
};

/* eslint-disable max-len,no-template-curly-in-string */
const baseConfig = require('@myparcel/semantic-release-config');
const {addExecPlugin, addGitHubPlugin, addGitPlugin} = require('@myparcel/semantic-release-config/src/plugins');

module.exports = {
  extends: '@myparcel/semantic-release-config',
  plugins: [
    ...baseConfig.plugins,
    addGitHubPlugin({
      assets: [
        {
          path: './dist/MyParcelNL-*.zip',
          label: 'Download MyParcelNL v${nextRelease.version} (for myparcel.nl customers)',
        },
        {
          path: './dist/MyParcelBE-*.zip',
          label: 'Download MyParcelBE v${nextRelease.version} (for sendmyparcel.be customers)',
        },
      ],
    }),
    addExecPlugin({
      prepareCmd: 'node ./private/updateVersion.js ${nextRelease.version} && npm run build',
    }),
    addGitPlugin(),
  ],
};

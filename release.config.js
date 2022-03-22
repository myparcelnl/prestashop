const baseConfig = require('@myparcel/semantic-release-config');
const {addExecPlugin, addGitHubPlugin, addGitPlugin} = require(
  '@myparcel/semantic-release-config/src/plugins',
);

module.exports = {
  extends: '@myparcel/semantic-release-config',
  plugins: [
    ...baseConfig.plugins,
    addGitHubPlugin({
      assets: [
        {path: './dist/MyParcelNL-*.zip', label: 'MyParcelNL v${nextRelease.version}'},
        {path: './dist/MyParcelBE-*.zip', label: 'MyParcelBE v${nextRelease.version}'},
      ],
    }),
    addExecPlugin({
      prepareCmd: 'node ./private/updateVersion.js ${nextRelease.version} && npm run build',
    }),
    addGitPlugin(),
  ],
};

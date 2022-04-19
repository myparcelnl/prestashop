const path = require('path');

module.exports = {
  chainWebpack: (config) => {
    config.optimization.minimize(process.env.NODE_ENV === 'production');
    config.plugins.delete('html');
    config.plugins.delete('preload');
    config.plugins.delete('prefetch');
  },
  configureWebpack: (config) => {
    if (process.env.NODE_ENV === 'development') {
      config.devtool = 'eval-source-map';
      config.output.devtoolModuleFilenameTemplate = (info) => {
        return info.resourcePath.match(/^\.\/\S*?\.vue$/)
          ? `webpack-generated:///${info.resourcePath}?${info.hash}`
          : `webpack-yourCode:///${info.resourcePath}`;
      };
      config.output.devtoolFallbackModuleFilenameTemplate = 'webpack:///[resource-path]?[hash]';
    }

    config.output.filename = '[name].js';
    config.output.chunkFilename = 'chunks/[name].js';
    config.resolve.alias['@'] = path.resolve(__dirname, 'src');
  },
  css: {
    extract: false,
  },
  runtimeCompiler: false,
  productionSourceMap: false,
  filenameHashing: false,
  // Output: /<module>/views/dist/js/admin
  outputDir: path.resolve(__dirname, '..', '..', 'dist', 'js', 'admin'),
  assetsDir: '',
};

const path = require('path');

module.exports = {
  chainWebpack: (config) => {
    config.optimization.minimize(process.env.NODE_ENV === 'production');
    config.plugins.delete('html');
    config.plugins.delete('preload');
    config.plugins.delete('prefetch');
  },
  configureWebpack: {
    devtool: 'sourcemap',
    output: {
      filename: '[name].js',
      chunkFilename: 'chunks/[name].js',
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      },
    },
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
  publicPath: '/modules/myparcelbe/views/dist/js/admin',
};

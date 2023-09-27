const customTsConfig = require('vite-plugin-custom-tsconfig');
const {mergeConfig} = require('vite');

/**
 * @param env {import('vite').Env}
 *
 * @return {import('vitest/config').UserConfig}
 */
const createDefaultConfig = (env) => {
  const isDev = env.mode === 'development';

  return {
    build: {
      minify: !isDev,
      sourcemap: isDev,
    },
    define: {
      'process.env': {},
    },
    plugins: [customTsConfig()],
    test: {
      coverage: {
        reporter: ['text', 'clover'],
      },
      environment: 'happy-dom',
      passWithNoTests: true,
    },
  };
};

/** @type createViteConfig {import('@myparcel-woocommerce/vite-config').createViteConfig} */
const createViteConfig = (config) => async (env) => {
  let resolvedConfig = config ?? {};

  if (typeof config === 'function') {
    resolvedConfig = await config(env);
  }

  return mergeConfig(createDefaultConfig(env), resolvedConfig);
};

module.exports = {createViteConfig};

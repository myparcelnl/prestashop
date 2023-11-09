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
      rollupOptions: {
        external: ['vite', 'vitest'],
      },
    },
    plugins: [customTsConfig()],
    test: {
      passWithNoTests: true,
      coverage: {
        all: true,
        enabled: false,
        reporter: ['text', 'clover'],
      },
    },
  };
};

/** @type createViteConfig {import('@myparcel-prestashop/vite-config').createViteConfig} */
const createViteConfig = (config) => async (env) => {
  let resolvedConfig = config ?? {};

  if (typeof config === 'function') {
    resolvedConfig = await config(env);
  }

  return mergeConfig(createDefaultConfig(env), resolvedConfig);
};

module.exports = {createViteConfig};

import {defineConfig} from 'vitest/config';

/**
 * @see https://vitejs.dev/config/
 */
export default defineConfig((env) => ({
  build: {
    emptyOutDir: false,
    lib: {
      entry: 'src/index.ts',
      formats: ['iife'],
      name: 'MyParcelFrontend',
    },
    minify: env.mode !== 'development',
    outDir: 'lib',
    sourcemap: env.mode === 'development',
  },
  test: {
    coverage: {
      reporter: ['text', 'clover'],
    },
    environment: 'happy-dom',
  },
}));

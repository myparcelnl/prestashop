import {defineConfig} from 'vitest/config';
import vue from '@vitejs/plugin-vue';

/**
 * @see https://vitejs.dev/config/
 */
export default defineConfig((env) => ({
  build: {
    emptyOutDir: false,
    lib: {
      entry: 'src/index.ts',
      formats: ['iife'],
      name: 'MyParcelAdmin',
    },
    minify: env.mode !== 'development',
    outDir: 'lib',
    rollupOptions: {
      external: ['vue'],
      output: {
        globals: {
          vue: 'Vue',
          pinia: 'Pinia',
          '@tanstack/vue-query': 'VueQuery',
        },
      },
    },
    sourcemap: env.mode === 'development',
  },

  define: {
    'process.env': {},
  },

  plugins: [vue()],

  test: {
    coverage: {
      reporter: ['text', 'clover'],
    },
    environment: 'happy-dom',
  },
}));

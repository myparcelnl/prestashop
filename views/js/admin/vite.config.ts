import {URL, fileURLToPath} from 'url';
import basicSsl from '@vitejs/plugin-basic-ssl';
import {defineConfig} from 'vitest/config';
import path from 'path';
import vue from '@vitejs/plugin-vue';

/**
 * @see https://vitejs.dev/config/
 */
export default defineConfig({
  plugins: [vue({}), basicSsl()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  // server: {
  //   port: LOCAL_PORT,
  //   https: true,
  // },
  build: {
    // minify: false,
    // cssCodeSplit: false,
    // sourcemap: true,
    // manifest: true,
    outDir: path.resolve(__dirname, '..', '..', 'dist', 'js', 'admin'),

    lib: {
      name: 'MyParcelPrestaShopAdmin',
      entry: 'src/index.ts',
      formats: ['cjs'],
    },
  },

  test: {
    environment: 'happy-dom',
    coverage: {
      reporter: ['text', 'clover'],
    },
  },
});

import {URL, fileURLToPath} from 'url';
import basicSsl from '@vitejs/plugin-basic-ssl';
import {defineConfig} from 'vite';
import path from 'path';
import vue from '@vitejs/plugin-vue';

const LOCAL_PORT = 9420;

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

  server: {
    port: LOCAL_PORT,
    https: true,
  },

  build: {
    minify: false,
    cssCodeSplit: false,
    sourcemap: true,
    manifest: true,

    outDir: path.resolve(__dirname, '..', '..', 'dist', 'js', 'admin'),
  },
});

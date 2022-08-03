module.exports = {
  root: true,
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: '@typescript-eslint/parser',
  },
  extends: [
    'plugin:prettier/recommended',
    'eslint:recommended',
    'plugin:vue/vue3-recommended',
  ],
};

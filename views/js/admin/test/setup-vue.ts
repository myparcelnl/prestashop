import Vue from 'vue';
import VueCompositionAPI from '@vue/composition-api';

Vue.use(VueCompositionAPI);

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
global.$ = jest.fn(() => ({
  ajax: jest.fn(),
  animate: jest.fn(),
  scrollTop: jest.fn(),
}));

// eslint-disable-next-line @typescript-eslint/ban-ts-comment
// @ts-ignore
global.jQuery = global.$;

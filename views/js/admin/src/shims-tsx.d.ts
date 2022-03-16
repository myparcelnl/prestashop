import Vue, { VNode } from 'vue';

declare global {
  namespace JSX {
    // tslint:disable no-empty-interface
    type Element = VNode;
    // tslint:disable no-empty-interface
    type ElementClass = Vue;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    type IntrinsicElements = Record<string, any>;
  }
}

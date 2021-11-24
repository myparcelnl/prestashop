declare module '*.vue' {
  import { defineComponent } from 'vue';
  const component: ReturnType<typeof defineComponent>;
  export default component;
}

declare module 'tiny-emitter' {
  import { TinyEmitter } from 'tiny-emitter';
  export default TinyEmitter;
}

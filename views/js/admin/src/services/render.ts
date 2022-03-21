import { ContextKey } from '@/data/global/context';
import { PackageFormat } from '@/data/global/definitions';
import Vue from 'vue';
import { createApp } from '@vue/composition-api';
import { filters } from '@/filters';

type RenderCallback = (selector: string, data?: Record<string, unknown>) => Promise<Vue>;
type Render = (componentPath: string) => RenderCallback;

/**
 * Create a function to render a new vue instance with given component.
 */
export const render: Render = (componentPath) => {
  const renderCallback: RenderCallback = async(selector, data?) => {
    const component = (await import (`@/${componentPath}`)).default;

    const app = createApp(component);

    app.use((instance) => {
      instance.prototype.$ContextKey = ContextKey;
      instance.prototype.$PackageFormat = PackageFormat;
      instance.prototype.$filters = filters;

      if (data) {
        instance.prototype.$instanceData = data;
      }
    });

    return app.mount(selector);
  };

  return renderCallback;
};

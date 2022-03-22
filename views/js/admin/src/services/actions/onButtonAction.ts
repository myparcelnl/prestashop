import { ContextKey } from '@/data/global/context';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

/**
 *
 */
export function onButtonAction(response: any, action: string): void {
  console.log(response);

  const context = useGlobalContext(ContextKey.MODULE_SETTINGS_FORM);

  const carrierSettings = context.value.find((item) => item.name === 'carrier-settings');

  if (carrierSettings) {
    carrierSettings.children = response.success;
  }
}

import { Ref, onBeforeUnmount, onMounted, ref } from '@vue/composition-api';
import { ContextKey } from '@/data/global/context';
import { createScript } from '@/services/createScript';
import { deliveryOptionsEventBus } from '@/data/eventBus/DeliveryOptionsEventBus';
import { isOfType } from '@/utils/type-guard/isOfType';
import { useGlobalContext } from '@/composables/context/useGlobalContext';

enum Events {
  UPDATED_DELIVERY_OPTIONS = 'myparcel_updated_delivery_options',
  UPDATE_DELIVERY_OPTIONS = 'myparcel_update_delivery_options',
}

type UseDeliveryOptions = (listener: (event: CustomEvent) => void) => {
  loaded: Ref<boolean>;
  htmlContent: Ref<string | null>;
};

/**
 * Loads an instance of the delivery options. `htmlContent` must be present in the DOM.
 */
export const useDeliveryOptions: UseDeliveryOptions = (listener) => {
  const htmlContent = ref<string | null>(null);
  const contextData = useGlobalContext(ContextKey.SHIPMENT_OPTIONS);
  const loaded = ref(false);

  onMounted(initialize);
  onBeforeUnmount(() => {
    document.removeEventListener(Events.UPDATED_DELIVERY_OPTIONS, listenerWrapper);
  });

  /**
   * Attach listener and load the delivery options.
   *
   * @returns {Promise<void>}
   */
  async function initialize(): Promise<void> {
    const { carrier } = contextData.value.deliveryOptions;
    const configuration = await deliveryOptionsEventBus.getConfiguration(carrier);
    htmlContent.value = '<div class="myparcel-delivery-options" />';

    document.addEventListener(Events.UPDATED_DELIVERY_OPTIONS, listenerWrapper);
    await loadScript();
    document.dispatchEvent(new CustomEvent(Events.UPDATE_DELIVERY_OPTIONS, {
      detail: { ...configuration.data, selector: '.myparcel-delivery-options' },
    }));
  }

  /**
   * Load the delivery options script.
   */
  async function loadScript(): Promise<void> {
    if (loaded.value) {
      return;
    }

    await createScript('/modules/myparcelbe/views/dist/js/external/myparcel.lib.js');
    loaded.value = true;
  }

  /**
   * Wraps the listener with a type guard to ensure the event can only be a CustomEvent.
   */
  const listenerWrapper = (event: Event): void => {
    if (!isOfType<CustomEvent>(event, 'detail')) {
      return;
    }

    listener(event);
  };

  return { loaded, htmlContent };
};

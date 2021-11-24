import { ComputedRef, computed, ref } from '@vue/composition-api';
import { EventBus, EventName } from '@/data/eventBus/EventBus';
import { convertErrorToAlertData } from '@/services/convertErrorToAlertData';
import { scrollToElement } from '@/utils/scrollToElement';
import { toArray } from '@/utils/toArray';

interface Composed {
  alerts: ComputedRef<AlertData[]>;
}

type UseEventBusAlerts = (eventBus: EventBus | EventBus[], errorElementSelector?: string) => Composed;

export const useEventBusAlerts: UseEventBusAlerts = (eventBuses, errorElementSelector) => {
  const localRef = ref<AlertData[]>([]);

  const alerts: ComputedRef<AlertData[]> = computed<AlertData[]>(() => {
    eventBuses = toArray<EventBus>(eventBuses);
    eventBuses.forEach((eventBus) => {
      eventBus.on(EventName.BUSY, ({ response: busy }) => {
        if (busy) {
          localRef.value = [];
        }
      });

      eventBus.on(EventName.ERROR, (data) => {
        localRef.value = convertErrorToAlertData(data);

        if (errorElementSelector) {
          scrollToElement(errorElementSelector);
        }
      });
    });

    return localRef.value;
  });

  return { alerts };
};

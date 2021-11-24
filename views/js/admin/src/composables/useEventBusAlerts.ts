import { ComputedRef, computed, ref } from '@vue/composition-api';
import { EventBus, EventName } from '@/data/eventBus/EventBus';
import { convertErrorToAlertData } from '@/services/convertErrorToAlertData';
import { toArray } from '@/utils/toArray';

interface Composed {
  alerts: ComputedRef<AlertData[]>;
}

type UseEventBusAlerts = (eventBus: EventBus|EventBus[]) => Composed;

export const useEventBusAlerts: UseEventBusAlerts = (eventBuses) => {
  const localRef = ref<AlertData[]>([]);

  const alerts: ComputedRef<AlertData[]> = computed<AlertData[]>(() => {
    eventBuses = toArray<EventBus>(eventBuses);
    eventBuses.forEach((eventBus) => {
      eventBus.on(EventName.BUSY, (busy: boolean) => {
        if (busy) {
          localRef.value = [];
        }
      });

      eventBus.on(EventName.ERROR, (error: ErrorResponse) => {
        localRef.value = convertErrorToAlertData(error);
      });
    });

    return localRef.value;
  });

  return { alerts };
};

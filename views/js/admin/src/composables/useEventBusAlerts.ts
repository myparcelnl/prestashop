import { EventBus, EventName } from '@/data/eventBus/EventBus';
import { Ref, ref } from '@vue/composition-api';
import { createMessagesCallback } from '@/composables/createMessagesCallback';
import { toArray } from '@/utils/toArray';

interface Composed {
  alerts: Ref<AlertData[]>;
  clear: () => void;
}

type UseEventBusAlerts = (eventBus?: EventBus | EventBus[], errorElementSelector?: string) => Composed;

let alerts: Ref<AlertData[]>;

export const useEventBusAlerts: UseEventBusAlerts = (eventBuses = [], errorElementSelector = undefined) => {
  alerts ??= ref<AlertData[]>([]);

  eventBuses = toArray<EventBus>(eventBuses);
  eventBuses.forEach((eventBus) => {
    eventBus.on(EventName.BUSY, ({ response: busy }) => {
      if (busy) {
        alerts.value = [];
      }
    });

    eventBus.on(EventName.ERROR, createMessagesCallback<EventName.ERROR>(alerts, 'danger', errorElementSelector));
    eventBus.on(EventName.RESPONSE, createMessagesCallback<EventName.RESPONSE>(alerts, 'success', errorElementSelector));
  });

  return {
    alerts,
    clear: (): void => {
      alerts.value = [];
    },
  };
};

import { EmitterRequestData, EventBus, EventName } from '@/data/eventBus/EventBus';
import { Ref } from '@vue/composition-api';
import { toArray } from '@/utils/toArray';
import { useLoading } from '@/composables/useLoading';

interface LoadingState {
  loading: Ref<boolean>;
}

interface UseEventBusLoadingState {
  (eventBus: EventBus): LoadingState;
  (eventBuses: EventBus[]): LoadingState;
  (...eventBuses: EventBus[]): LoadingState;
}

/**
 * Used to listen to one or more eventbuses and set a loading state based on whether it's busy.
 */
export const useEventBusLoadingState: UseEventBusLoadingState = (input) => {
  const eventBuses = toArray(input);
  const { loading, setLoading } = useLoading();

  const setEventBusLoading = (data: EmitterRequestData<boolean>): void => {
    setLoading(data.response);
  };

  eventBuses.forEach((eventBus) => {
    eventBus.on(EventName.BUSY, setEventBusLoading);
  });

  return {
    loading,
  };
};

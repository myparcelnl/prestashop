import { Event, useVModel } from '@/composables/form/useVModel';

export const useModel: ComposableComponentWithSetup<ReturnType<typeof useVModel>> = (
  event: Event = 'input',
  prop: string = 'value',
) => ({
  emits: [event],
  model: {
    event,
    prop,
  },
  props: {
    value: {
      required: true,
    },
  },
  setup: (props: ReturnType<typeof useModel>['props'], ctx) => useVModel(props.value, ctx, event),
});

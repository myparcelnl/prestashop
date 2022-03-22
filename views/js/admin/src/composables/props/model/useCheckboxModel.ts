import { PropType } from '@vue/composition-api';
import { useCheckboxVModel } from '@/composables/form/useCheckboxVModel';
import { useModel } from '@/composables/props/model/useModel';
import { useVModel } from '@/composables/form/useVModel';

export const useCheckboxModel: ComposableComponentWithSetup<ReturnType<typeof useCheckboxVModel>> = () => ({
  ...useModel('change', 'checked'),
  props: {
    value: {
      type: [String, Number],
      default: null,
    },
    checked: {
      type: [Boolean, Array] as PropType<boolean | string[]>,
      default: false,
    },
  },
  setup: useCheckboxVModel,
});

export const useMultipleCheckboxModel: ComposableComponentWithSetup<ReturnType<typeof useCheckboxVModel>> = () => ({
  ...useModel('change', 'value'),
  props: {
    value: {
      type: Array as PropType<string[]>,
      default: null,
    },
  },
  setup: (props: ReturnType<typeof useModel>['props'], ctx) => useVModel(props.value, ctx, 'change'),
});

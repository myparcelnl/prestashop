import { PropType } from '@vue/composition-api';
import { useCheckboxVModel } from '@/composables/form/useCheckboxVModel';
import { useModel } from '@/composables/props/model/useModel';

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

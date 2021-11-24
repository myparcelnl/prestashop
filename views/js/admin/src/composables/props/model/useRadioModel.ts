import { useCheckboxVModel } from '@/composables/form/useCheckboxVModel';
import { useModel } from '@/composables/props/model/useModel';

export const useRadioModel: ComposableComponentWithSetup<ReturnType<typeof useCheckboxVModel>> = () => ({
  ...useModel('change', 'checked'),
  props: {
    checked: {
      type: String,
      default: null,
    },
  },
  setup: useCheckboxVModel,
});

import { Data, SetupContext, WritableComputedRef, computed } from '@vue/composition-api';

/**
 * V-model composable specifically for checkboxes.
 */
export const useCheckboxVModel = (
  props: Data,
  ctx: SetupContext,
): {
  mutableValue: WritableComputedRef<unknown>;
} => {
  return {
    mutableValue: computed({
      get(): unknown {
        return props.checked;
      },
      set(newValue: unknown): void {
        ctx.emit('change', newValue);
      },
    }),
  };
};

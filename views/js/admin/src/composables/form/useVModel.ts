import { SetupContext, WritableComputedRef, computed, ref } from '@vue/composition-api';

export type Event = string | 'input' | 'change';

/**
 * Composable for using v-model events.
 */
export const useVModel = (
  propValue: unknown,
  ctx: SetupContext,
  event: Event = 'input',
): { mutableValue: WritableComputedRef<unknown> } => {
  const localValue = ref<unknown>(propValue);
  const mutableValue = computed({
    get(): unknown {
      return localValue.value;
    },
    set(newValue: unknown): void {
      localValue.value = newValue;
      ctx.emit(event, newValue);
    },
  });

  return { mutableValue };
};

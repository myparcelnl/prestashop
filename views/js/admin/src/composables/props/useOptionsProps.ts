import { PropType } from 'vue';

export const useOptionsProps = <Type = SelectOption>() => ({
  options: {
    type: Array as () => PropType<Type[]>,
    default: (): never[] => [],
  },
});

import { PropType } from 'vue';

interface OptionsProp<Type = SelectOption> {
  options: {
    default: () => never[];
    type: () => PropType<Type[]>;
  };
}

export const useOptionsProps = <Type = SelectOption>(): OptionsProp<Type> => ({
  options: {
    type: Array as () => PropType<Type[]>,
    default: (): never[] => [],
  },
});

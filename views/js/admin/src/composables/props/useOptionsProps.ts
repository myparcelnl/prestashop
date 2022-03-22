import { PropType } from 'vue';

interface OptionsProp<Type = SelectOption> {
  options: {
    type: () => PropType<Type[]>;
    default: () => never[];
  };
}

export const useOptionsProps = <Type = SelectOption>(): OptionsProp<Type> => ({
  options: {
    type: Array as () => PropType<Type[]>,
    default: (): never[] => [],
  },
});

import { PropType } from '@vue/composition-api';

export const variantProps = (defaultValue?: Variant): Props => ({
  variant: {
    type: String as PropType<Variant>,
    default: defaultValue,
  },
});

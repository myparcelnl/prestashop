import { Context, ContextKey, MyParcelContext } from '@/data/global/context';
import { getContext } from '@/data/global/getContext';
import { isEnumValue } from '@/utils/type-guard/isEnumValue';
import { reactive } from '@vue/composition-api';

interface UseContext {
  <Key extends ContextKey>(context: MyParcelContext<Key> | Key): MyParcelContext<Key>;
  <Key extends ContextKey>(contextKey: Key): Context;
}

export const useContext: UseContext = (context) => {
  let contextData = null;

  if (!context) {
    throw new Error('Failed to get context object.');
  }

  if (isEnumValue(context, ContextKey)) {
    contextData = reactive(getContext(context));
  } else {
    contextData = context;
  }

  return contextData;
};

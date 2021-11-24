import { Context, ContextKey, MyParcelContext } from '@/data/global/context';
import { isEnumValue } from '@/utils/type-guard/isEnumValue';

interface GetContext {
  <C extends ContextKey>(contextKey: C): MyParcelContext<C>;
  <C extends ContextKey>(contextKey: C): Context;
}

/**
 * Get a context entry from the window object.
 */
export const getContext: GetContext = (contextKey) => {
  if (!isEnumValue(contextKey, ContextKey)) {
    throw new Error(`Could not get context for key ${contextKey}`);
  }

  return window.MyParcelContext?.[contextKey];
};

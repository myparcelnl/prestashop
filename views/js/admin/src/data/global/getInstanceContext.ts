import { ContextKey, MyParcelContext } from '@/data/global/context';
import Vue from 'vue';

/**
 * Get context from instance data.
 */
export function getInstanceContext<T extends ContextKey>(contextKey: T): MyParcelContext<T> {
  if (!Vue.prototype.$instanceData.hasOwnProperty(contextKey)) {
    throw new Error(`Context ${contextKey} not found in instance data.`);
  }

  return Vue.prototype.$instanceData[contextKey];
}

import { AnyContext, ContextKey, MyParcelContext } from '@/data/global/context';
import { WritableComputedRef, computed, ref } from '@vue/composition-api';
import { useContext } from '@/composables/context/useContext';

type ContextRef<Key extends ContextKey> = WritableComputedRef<MyParcelContext<Key>>;

interface UseGlobalContext {
  <Key extends ContextKey>(contextKey: Key, context?: MyParcelContext<Key> | null): ContextRef<Key>;
  <Key extends ContextKey>(contextKey: Key, context: undefined): ContextRef<Key>;
}

const cache: ContextRef<ContextKey>[] = [];

/**
 * Get a globally defined context, filtered by context.id and context.orderId.
 */
export const useGlobalContext: UseGlobalContext = (contextKey, context?) => {
  const orderId = computed(() => context?.orderId ?? null);
  const localEntry = ref<WritableComputedRef<AnyContext>>();

  return computed({
    get() {
      let foundEntry = cache.find((item) => {
        const orderIdMatches = orderId.value ? orderId.value === item.value.orderId : true;
        return contextKey === item.value.id && orderIdMatches;
      });

      if (!foundEntry) {
        const localContext = ref<AnyContext | null>(null);
        const createdEntry = computed({
          get() {
            return localContext.value ?? useContext(context ?? contextKey);
          },
          set(context: AnyContext) {
            localContext.value = context;
          },
        });

        cache.push(createdEntry);
        foundEntry = createdEntry;
      }

      localEntry.value = foundEntry;
      return foundEntry.value;
    },

    set(context: AnyContext) {
      if (localEntry.value) {
        localEntry.value.value = context;
      }
    },
  });
};

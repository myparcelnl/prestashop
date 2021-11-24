import { Ref, ref } from '@vue/composition-api';

type UseLoading = (initialValue?: boolean) => {loading: Ref<boolean>; setLoading: (state: boolean) => void};

/**
 * Manages loading state.
 */
export const useLoading: UseLoading = (initialValue = false) => {
  const loading = ref<boolean>(initialValue);
  const setLoading = (state: boolean): void => {
    loading.value = state;
  };

  return { loading, setLoading };
};

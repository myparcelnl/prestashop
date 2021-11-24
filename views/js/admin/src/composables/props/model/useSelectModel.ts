import { useModel } from '@/composables/props/model/useModel';

export const useSelectModel = (): ReturnType<typeof useModel> => useModel('change');

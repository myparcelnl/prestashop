import { Ref, ref } from '@vue/composition-api';

type UseNotices = () => {
  notices: Ref<AlertData[]>;
  addNotice: AddNotice;
};

type AddNotice = (notice: AlertData) => void;

let notices: Ref<AlertData[]>;

/**
 * Manages global notices.
 */
export const useNotices: UseNotices = () => {
  if (!notices) {
    notices = ref<AlertData[]>([]);
  }

  const addNotice: AddNotice = (notice) => {
    notices.value.push(notice);
  };

  return {
    notices,
    addNotice,
  };
};

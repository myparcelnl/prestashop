import { Ref, ref } from '@vue/composition-api';
import { getAdminUrl } from '@/services/ajax/getAdminUrl';

let pdfWindow: Ref<Window | null>;

type UsePdfWindow = () => {
  pdfWindow: typeof pdfWindow;
  open: () => void;
  close: () => void;
  navigate: (href: string) => void;
};

/**
 * Keeps a reference to a window.
 */
export const usePdfWindow: UsePdfWindow = () => {
  const open: ReturnType<UsePdfWindow>['open'] = () => {
    pdfWindow = ref<Window | null>(window.open(getAdminUrl(window.MyParcelActions.pathLoading), '_blank'));
    if (!pdfWindow.value) {
      throw new Error('Failed to create new window.');
    }

    window.focus();
    pdfWindow.value?.blur();
    pdfWindow.value.onclose = close;
  };

  const close: ReturnType<UsePdfWindow>['close'] = () => {
    if (!pdfWindow?.value) {
      return;
    }

    pdfWindow.value.close();
    pdfWindow.value = null;
  };

  const navigate: ReturnType<UsePdfWindow>['navigate'] = (href) => {
    if (!pdfWindow?.value) {
      return;
    }

    pdfWindow.value.location.assign(href);
  };

  return {
    pdfWindow,
    open,
    close,
    navigate,
  };
};

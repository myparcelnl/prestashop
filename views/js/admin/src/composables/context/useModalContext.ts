import { Ref, onMounted, ref } from '@vue/composition-api';
import { AnyContext } from '@/data/global/context';
import { useLoading } from '@/composables/useLoading';

export type ModalData = Record<never, unknown> | null | undefined;

export type ModalCallback = ((id: string) => Promise<void> | void);

export interface ModalCallbackProps {
  onSave: ModalCallback;
  onLeave: ModalCallback;
}

type UseModalContext = (modalId: Ref<string>, onSave: null | ModalCallback, onLeave: null | ModalCallback) => {
  additionalContext: Ref<AnyContext | null>;
  loading: Ref<boolean>;
  onButtonClick: (type: string) => Promise<void>;
  shown: Ref<boolean>;
  modalData: Ref;
};

export const useModalContext: UseModalContext = (modalId, onSave, onLeave) => {
  const { loading, setLoading } = useLoading();
  const shown = ref<boolean>(false);
  const additionalContext = ref<AnyContext | null>(null);
  const modalData = ref();

  onMounted(() => {
    void jQuery.ready.then(() => {
      const $modal = jQuery(`#${modalId.value}`);
      $modal
        .on('show.bs.modal', (e: BaseJQueryEventObject) => {
          const callerElement = e.relatedTarget ?? document.activeElement;
          if (callerElement) {
            const context = callerElement?.getAttribute('data-context');
            additionalContext.value = context ? JSON.parse(context) : null;
          }

          modalData.value = $modal.data('data');
          shown.value = true;
        })
        .on('hidden.bs.modal', () => {
          modalData.value = null;
          shown.value = false;
        });
    });
  });

  const onButtonClick = async(type: string): Promise<void> => {
    const callback = type === 'save' ? onSave : onLeave;

    if (callback) {
      setLoading(true);
      await callback(modalId.value);
      setLoading(false);
    }

    jQuery(`#${modalId.value}`).modal('hide');
  };

  return {
    additionalContext,
    loading,
    onButtonClick,
    shown,
    modalData,
  };
};

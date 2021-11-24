import { ModalData } from '@/composables/context/useModalContext';

/**
 * Open a modal and wait for it to be closed. Resolves true if saved, false if closed in any other way.
 */
export async function waitForModalClose(modalId: string, modalData?: ModalData): Promise<boolean> {
  const $modal = $(`#${modalId}`);
  if (!$modal.length) {
    throw new Error(`Modal #${modalId} not found.`);
  }

  $modal.data('data', modalData ?? null);
  $modal.modal('show');

  return new Promise((resolve) => {
    const callback = (): void => {
      resolve(document.activeElement?.getAttribute('data-type') === 'save');
      $modal.off('hide.bs.modal', callback);
    };

    $modal.on('hide.bs.modal', callback);
  });
}

import { usePdfWindow } from '@/composables/usePdfWindow';

/**
 * Creates a new window from given base64 encoded pdf string.
 */
export function openPdfInNewWindow(pdf: string): void {
  const byteArray = Uint8Array.from(atob(pdf), (element) => element.charCodeAt(0));

  const file = new Blob([byteArray], { type: 'application/pdf;base64' });
  const fileURL = URL.createObjectURL(file);

  const { pdfWindow } = usePdfWindow();

  if (pdfWindow.value) {
    pdfWindow.value.document.dispatchEvent(new CustomEvent('myparcel_label_ready', { detail: fileURL }));
  }
}

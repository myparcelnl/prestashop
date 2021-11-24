import { ActionResponse, printActions } from '@/data/global/actions';
import { downloadPdf } from '@/services/actions/downloadPdf';
import { openPdfInNewWindow } from '@/services/openPdfInNewWindow';

/**
 * After a request that printed labels, check the response for a pdf link or
 *  base64 encoded binary data and display/open accordingly.
 */
export function onPrintLabels(response: ActionResponse<typeof printActions[number]>): void {
  const { pdf } = response.data;

  if (pdf.startsWith('http')) {
    void downloadPdf(pdf);
    return;
  }

  openPdfInNewWindow(pdf);
}

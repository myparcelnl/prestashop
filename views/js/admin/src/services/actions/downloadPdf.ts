/**
 * Try to get the pdf url. An error means the pdf file is not ready yet. Retry until it is, then download the label.
 */
export async function downloadPdf(pdf: string): Promise<void> {
  try {
    await jQuery.ajax({ url: pdf, type: 'GET' });
  } catch (e) {
    await new Promise((resolve) => {
      setTimeout(resolve, 100);
    });

    return downloadPdf(pdf);
  }

  const anchor = document.createElement('a');
  anchor.href = pdf;
  anchor.download = '1';
  anchor.dispatchEvent(new MouseEvent('click'));
}

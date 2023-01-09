export const getDeliveryOptionsRow = (): JQuery | null => {
  const row = $('.delivery-option input:checked').closest('.delivery-option');
  return row.length ? row : null;
};

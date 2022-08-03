export const getElement = (selector: string): JQuery | null => {
  const element = $(selector);
  return element.length ? element : null;
};

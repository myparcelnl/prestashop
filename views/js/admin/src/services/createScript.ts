/**
 * Creates either a script or a link tag depending on the given url to load an external resource programmatically.
 *
 * @param {string} url - URL to load.
 * @returns {Promise}
 */
export const createScript = async(url: string): Promise<void> => {
  return new Promise((resolve) => {
    let tag;
    const attributes = [];

    if (url.endsWith('css')) {
      tag = 'link';
      attributes.push(['href', url]);
      attributes.push(['rel', 'stylesheet']);
    } else {
      tag = 'script';
      attributes.push(['defer', true]);
      attributes.push(['src', url]);
    }

    const loadable = document.createElement(tag);

    attributes.forEach(([attribute, value]) => {
      loadable.setAttribute(attribute.toString(), value.toString());
    });

    loadable.onload = (): void => {
      resolve();
    };

    document.head.appendChild(loadable);
  });
};

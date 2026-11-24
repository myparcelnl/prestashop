import {vi} from 'vitest';

/** happy-dom has no layout: give an element boxes unless it is inside a [hidden] element. */
export const mockLayout = (): void => {
  vi.spyOn(Element.prototype, 'getClientRects').mockImplementation(function (this: Element) {
    return (this.closest('[hidden]') ? [] : [new DOMRect(0, 0, 1, 1)]) as unknown as DOMRectList;
  });
};

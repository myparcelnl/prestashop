import { isOfType } from '@/utils/type-guard/isOfType';

/**
 * Scrolls to element with given selector.
 */
export function scrollToElement(selector?: string): void {
  if (!selector) {
    return;
  }

  const element = document.querySelector(selector);

  if (!element || !isOfType<HTMLElement>(element, 'offsetTop')) {
    throw new Error('Element not found or invalid: ' + selector);
  }

  $([
    document.documentElement,
    document.body,
  ]).animate({
    scrollTop: $(selector).scrollTop(),
  });
}

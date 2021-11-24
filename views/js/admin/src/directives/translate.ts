import { DirectiveFunction } from 'vue';
import { translate as translateFilter } from '../filters/translate';

/**
 * Translate binding.
 */
export const translate: DirectiveFunction = (el, bindings) => {
  el.innerText = translateFilter(bindings.value);
  return el;
};

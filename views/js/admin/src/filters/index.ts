import { formatDate } from '@/filters/formatDate';
import { translate } from '@/filters/translate';

/**
 * Not using actual Vue 2 filters to be ready for Vue 3.
 *
 * @see https://v3.vuejs.org/guide/migration/filters.html#_3-x-update
 */
export const filters = {
  translate,
  formatDate,
};

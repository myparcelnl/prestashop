import { getDateFormatKey } from '@/filters/getDateFormatKey';
import { padNumber } from '@/filters/padNumber';

export const formatDate = (dateString: string, format: DateFormat = 'lite'): string => {
  if (!dateString) {
    return '(No date)';
  }

  const key = getDateFormatKey(format);
  const date = new Date(dateString);

  const formatString = window.MyParcelConfiguration[key];

  return formatString
    .replace('d', padNumber(date.getDate()))
    .replace('m', padNumber(date.getMonth() + 1))
    .replace('Y', date.getFullYear().toString())
    .replace('H', padNumber(date.getHours()))
    .replace('i', padNumber(date.getMinutes()))
    .replace('s', padNumber(date.getSeconds()));
};

export const formatOptions: SelectOption<LabelFormat>[] = [
  {
    label: 'format_a4',
    value: 'a4',
  },
  {
    label: 'format_a6',
    value: 'a6',
  },
];

const TOP_LEFT: LabelPosition = '1';
const TOP_RIGHT: LabelPosition = '2';
const BOTTOM_LEFT: LabelPosition = '3';
const BOTTOM_RIGHT: LabelPosition = '4';

export const positions: LabelPosition[] = [TOP_LEFT, TOP_RIGHT, BOTTOM_LEFT, BOTTOM_RIGHT];

export const positionOptions: SelectOption<LabelPosition>[] = [
  {
    label: 'positions_top_left',
    value: TOP_LEFT,
  },
  {
    label: 'positions_top_right',
    value: TOP_RIGHT,
  },
  {
    label: 'positions_bottom_left',
    value: BOTTOM_LEFT,
  },
  {
    label: 'positions_bottom_right',
    value: BOTTOM_RIGHT,
  },
];

export const outputOptions: SelectOption<LabelOutput>[] = [
  {
    label: 'output_download',
    value: 'download',
  },
  {
    label: 'output_open',
    value: 'open',
  },
];

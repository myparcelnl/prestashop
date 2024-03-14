export const getAllEvents = (element: HTMLElement): string => {
  const excluded = [
    'click',
    'mousedown',
    'mouseenter',
    'mouseleave',
    'mousemove',
    'mouseout',
    'mouseover',
    'mouseup',
    'mousewheel',
    'pointerdown',
    'pointerenter',
    'pointerleave',
    'pointermove',
    'pointerout',
    'pointerover',
    'pointerrawupdate',
    'pointerup',
    'wheel',
  ];

  const result: string[] = [];

  for (const key in element) {
    if (key.startsWith('on')) {
      result.push(key.slice(2));
    }
  }

  return result.filter((item) => !excluded.includes(item)).join(' ');
};

import {getElement} from './jquery/getElement';

export const hasUnRenderedDeliveryOptions = (): boolean => Boolean(getElement('#myparcel-delivery-options'));

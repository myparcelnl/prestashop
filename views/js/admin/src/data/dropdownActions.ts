import { LabelAction } from '@/data/global/actions';

export const deleteAction: DropdownButtonItem = {
  label: 'action_delete',
  action: LabelAction.DELETE,
  icon: 'delete',
  variant: 'danger',
};

export const refreshAction: DropdownButtonItem = {
  label: 'action_refresh',
  action: LabelAction.REFRESH,
  icon: 'refresh',
};

export const returnAction: DropdownButtonItem = {
  label: 'action_create_return_label',
  action: LabelAction.CREATE_RETURN_LABEL,
  icon: 'reply',
};

export const printAction: DropdownButtonItem = {
  label: 'action_print',
  action: LabelAction.PRINT,
  icon: 'print',
};

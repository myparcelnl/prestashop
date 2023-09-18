import {globalLogger} from '@myparcel-pdk/admin-core';
import {AdminAction, useActionStore} from '@myparcel-pdk/admin';

const BULK_ACTION_PREFIX = 'myparcelnl';

const BULK_ACTION_MAP = Object.freeze({
  action_edit: AdminAction.OrdersEdit,
  action_export: AdminAction.OrdersExport,
  action_export_print: AdminAction.OrdersExportPrint,
  action_print: AdminAction.OrdersPrint,
});

export const listenForBulkActions = (): void => {
  Object.entries(BULK_ACTION_MAP).forEach(([key, action]) => {
    const button = document.querySelector<HTMLElement>(`.${BULK_ACTION_PREFIX}-${key}`);

    if (!button) {
      globalLogger.error(`Could not find bulk action button for ${key}`);
      return;
    }

    button.addEventListener('click', (event) => {
      const actionStore = useActionStore();

      const orderCheckboxes = document.querySelectorAll<HTMLInputElement>('.js-bulk-action-checkbox:checked');

      void actionStore.dispatch(action, {orderIds: [...orderCheckboxes].map((el) => el.value)});
    });
  });
};

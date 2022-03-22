import '@/directives';
import Vue from 'vue';
import VueCompositionAPI from '@vue/composition-api';
import VueRouter from 'vue-router';
import { attachBulkActionHandlers } from '@/services/bulk-actions/attachBulkActionHandlers';
import { render } from '@/services/render';
import SettingsFormItem from '@/components/module-settings/SettingsFormItem.vue';

/**
 *
 */
export function boot() {
  Vue.use(VueCompositionAPI);
  Vue.use(VueRouter);

  window.Vue = window.Vue ?? Vue;

  Vue.component('SettingsFormItem', SettingsFormItem);

  window.MyParcel = {
    renderAfterHeader: render('components/after-header'),
    renderLoadingPage: render('components/loading-page'),
    renderOrderCard: render('components/order-card'),
    renderOrderListColumn: render('components/order-list-column'),
    renderModuleSettings: render('components/module-settings'),
  };

  void jQuery.ready.then(attachBulkActionHandlers);
}

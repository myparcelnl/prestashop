import '@/publicPath';
import '@/directives';
import Vue from 'vue';
import VueCompositionAPI from '@vue/composition-api';
import { attachBulkActionHandlers } from '@/services/bulk-actions/attachBulkActionHandlers';
import { render } from '@/services/render';

Vue.use(VueCompositionAPI);

window.Vue = window.Vue ?? Vue;

window.MyParcel = {
  renderAfterHeader: render('components/after-header'),
  renderLoadingPage: render('components/loading-page'),
  renderOrderCard: render('components/order-card'),
  renderOrderListColumn: render('components/order-list-column'),
};

void jQuery.ready.then(attachBulkActionHandlers);

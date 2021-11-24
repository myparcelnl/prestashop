import './directives';
import Vue from 'vue';
import VueCompositionAPI from '@vue/composition-api';
import { attachBulkActionHandlers } from '@/services/bulk-actions/attachBulkActionHandlers';
import { render } from '@/services/render';

Vue.use(VueCompositionAPI);

window.Vue = window.Vue ?? Vue;

window.MyParcel = {
  renderAfterHeader: render('views/AfterHeader'),
  renderLoadingPage: render('views/LoadingPage'),
  renderOrderGridCard: render('views/OrderGridCard'),
  renderOrderListColumn: render('views/OrderListColumn'),
  renderProductSettings: render('views/ProductSettings'),
};

void jQuery.ready.then(attachBulkActionHandlers);

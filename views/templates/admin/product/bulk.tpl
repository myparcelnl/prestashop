{*
 * 2017-2019 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@dmp.nl so we can send you a copy immediately.
 *
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2019 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{include file="../../hook/load_webpack_chunks.tpl"}
<script type="text/javascript">
  (function initMyParcelDefaultCustomsInfo() {
    if (typeof window.MyParcelModule === 'undefined'
      || typeof window.MyParcelModule.app === 'undefined'
      || typeof window.MyParcelModule.app.default === 'undefined'
      || typeof window.MyParcelModule.app.default.productCustomsInfoLegacy === 'undefined'
    ) {
      return setTimeout(initMyParcelDefaultCustomsInfo, 10);
    }

    function documentReady(fn) {
      if (document.readyState !== 'loading') {
        fn();
      } else if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', fn);
      } else {
        document.attachEvent('onreadystatechange', function() {
          if (document.readyState !== 'loading')
            fn('myparcel-default-product-settings');
        });
      }
    }

    documentReady(function () {
      window.MyParcelModule.app.default.productCustomsInfoLegacy().then(function (fn) {
        fn.default(
          {
            idProduct: 0,
            prefix: 'myparcel',
            translations: {include file="../translations.tpl"},
            installationStep: {if MyParcelGoodsNomenclature::isInstalled()}0{else}1{/if},
            urls: {
              assets: '{$mpAssetsUrl|escape:'javascript':'UTF-8'}',
              customsInfoBulk: '{$mpProductSettingsBulkUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureInstall: '{$mpGoodsNomenclatureInstallUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureSearch: '{$mpGoodsNomenclatureSearchUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureBrowse: '{$mpGoodsNomenclatureBrowseUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureNavigate: '{$mpGoodsNomenclatureNavigateUrl|escape:'javascript':'UTF-8'}',
            },
            countries: {mypa_json_encode($mpJsCountries)},
            theme: {
              theme: '{MyParcel::getThemeVersion('product')|escape:'javascript':'UTF-8'}',
            },
            defaults: {
              classification: '',
              country: 'SKIP',
              status: 'disable',
              ageCheck: false,
              cooledDelivery: false,
            }
          },
          '{$smarty.const._PS_VERSION_|escape:'javascript':'UTF-8'}',
          'bulk',
        )
      });
    });
  }());
</script>

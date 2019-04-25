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
<div class="panel">
  {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '>=') && version_compare($smarty.const._PS_VERSION_, '1.7.0.0', '<')}
  <div class="panel-heading">
    <img alt="MyParcel logo" src="/modules/myparcel/views/img/myparcelnl-grayscale.png" style="width: 16px; height: 16px"> MyParcel - {l s='Default customs info' mod='myparcel'}
  </div>
  {/if}
  <div class="panel-body">
    <div class="alert alert-info">
      {l s='On this tab you can configure what information shows up on the generated customs form.' mod='myparcel'}<br />
      {l s='It wil be automatically generated when all information is available, so make sure you have set the weight of this product as well.' mod='myparcel'}</div>
    <div id="myparcel-default-product-settings">
      <style type="text/css" scoped>
        @keyframes animation-14kwaps {
          0%, 80%, 100% {
            -webkit-transform: scale(0);
            -ms-transform: scale(0);
            transform: scale(0);
          }
          40% {
            -webkit-transform: scale(1.0);
            -ms-transform: scale(1.0);
            transform: scale(1.0);
          }
        }

        .spinner-div {
          margin: 20px auto;
        }

        .spinner-div div {
          width: 18px;
          height: 18px;
          border-radius: 100%;
          display: inline-block;
          -webkit-animation: animation-14kwaps 1.4s infinite ease-in-out both;
          animation: animation-14kwaps 1.4s infinite ease-in-out both;
        }

        .spinner-div .bounce1 {
          -webkit-animation-delay: 0s;
          animation-delay: 0s;
          background-color: #555;
          margin: 0 2px;
        }
        .spinner-div .bounce2 {
          -webkit-animation-delay: -0.16s;
          animation-delay: -0.16s;
          background-color: #555;
          margin: 0 2px;
        }
        .spinner-div .bounce3 {
          -webkit-animation-delay: -0.32s;
          animation-delay: -0.32s;
          background-color: #555;
          margin: 0 2px;
        }
      </style>
      <div class="spinner-div">
        <div class="bounce3"></div>
        <div class="bounce2"></div>
        <div class="bounce1"></div>
      </div>
    </div>
  </div>
  {if version_compare($smarty.const._PS_VERSION_, '1.6.0.0', '>=') && version_compare($smarty.const._PS_VERSION_, '1.7.0.0', '<')}
    <div class="panel-footer">
      <button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save'}</button>
      <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save and stay'}</button>
    </div>
  {/if}
</div>


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
            idProduct: {$mpIdProduct|intval},
            prefix: 'myparcel',
            installationStep: {if MyParcelGoodsNomenclature::isInstalled()}0{else}1{/if},
            urls: {
              assets: '{$mpAssetsUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureInstall: '{$mpGoodsNomenclatureInstallUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureSearch: '{$mpGoodsNomenclatureSearchUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureBrowse: '{$mpGoodsNomenclatureBrowseUrl|escape:'javascript':'UTF-8'}',
              goodsNomenclatureNavigate: '{$mpGoodsNomenclatureNavigateUrl|escape:'javascript':'UTF-8'}',
            },
            theme: {
              theme: '{MyParcel::getThemeVersion('product')|escape:'javascript':'UTF-8'}',
            },
            defaults: {
              classification: '{$mpProductSettings->classification|escape:'javascript':'UTF-8'}',
              country: '{$mpProductSettings->country|escape:'javascript':'UTF-8'}',
              status: '{if $mpProductSettings->status === MyParcelProductSetting::CUSTOMS_ENABLE}enable{elseif $mpProductSettings->status === MyParcelProductSetting::CUSTOMS_SKIP}skip{else}disable{/if}',
              ageCheck: {if $mpProductSettings->age_check}true{else}false{/if},
              cooledDelivery: {if $mpProductSettings->cooled_delivery}true{else}false{/if},
            },
            translations: {include file="../translations.tpl"},
            countries: {mypa_json_encode($mpJsCountries)},
          },
          '{$smarty.const._PS_VERSION_|escape:'javascript':'UTF-8'}',
          'product',
          'myparcel-default-product-settings',
        );
      });
    });
  }());
</script>

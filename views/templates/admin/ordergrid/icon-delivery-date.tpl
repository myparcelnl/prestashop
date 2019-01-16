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
{if in_array(strtoupper($tr['myparcel_country_iso']), ['NL', 'BE'])}
  {if !$tr['myparcel_pickup'] && $deliveryDate > '1970-01-02 00:00:00'}
    <span class="label label-{$badgeType|escape:'htmlall':'UTF-8'}">
      <i class="icon icon-home"></i>&nbsp;&nbsp;{$deliveryDate|escape:'htmlall':'UTF-8'}
    </span>
  {elseif $tr['myparcel_pickup']}
    {if in_array(strtoupper($tr['myparcel_country_iso']), ['NL', 'BE'])}
      <span class="label label-{$badgeType|escape:'htmlall'} pickup-tooltip-{$tr['id_order']|intval}" title="{$tr['myparcel_pickup']|escape:'htmlall':'UTF-8'}">
        <i class="icon icon-building"></i>&nbsp;&nbsp;{$deliveryDate|escape:'htmlall':'UTF-8'}
      </span>
      <script type="text/javascript">
        (function () {
          function init() {
            if (typeof $ === 'undefined') {
              setTimeout(init, 100);
              return;
            }
            $('.pickup-tooltip-{$tr['id_order']|intval}').tooltip();
          }
          init();
        }());
      </script>
    {else}
      <span class="label label-info">
        <i class="icon icon-building"></i>&nbsp;&nbsp;{l s='Unknown' mod='myparcel'}
      </span>
    {/if}
  {else}
    <span class="label label-info">
      <i class="icon icon-question-circle"></i>&nbsp;&nbsp;{l s='Unknown' mod='myparcel'}
    </span>
  {/if}
{else}
  <span class="label label-info">
    <i class="icon icon-globe"></i>&nbsp;&nbsp;{l s='Unknown' mod='myparcel'}
  </span>
{/if}

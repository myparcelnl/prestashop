{*
 * 2017-2018 DM Productions B.V.
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
 * @copyright  2010-2018 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<div class="info-order box">
  <h3 class="page-subheading">{l s='PostNL delivery' mod='myparcel'}</h3>
  {foreach name=shipmentLoop from=$shipments item=shipment}
    <h4>{l s='Shipment' mod='myparcel'} {$smarty.foreach.shipmentLoop.index + 1|intval nofilter}</h4>
    {if isset($shipment['tracktrace']) && $shipment['tracktrace']}
      <strong>{l s='Track & Trace:' mod='myparcel'}</strong>
      <a href="http://postnl.nl/tracktrace/?L={$languageIso|escape:'htmlall':'UTF-8' nofilter}&B={$shipment['tracktrace']|escape:'htmlall':'UTF-8' nofilter}&P={$shipment['postcode']|escape:'htmlall':'UTF-8' nofilter}&D=NL&T=C"
         target="_blank">
        {$shipment['tracktrace']|escape:'htmlall':'UTF-8' nofilter}
      </a>
    {/if}
    <table class="table table-bordered">
      <thead>
        <tr>
          <th class="first_item">{l s='PostNL Status' mod='myparcel'}</th>
          <th class="last_item">{l s='Date updated' mod='myparcel'}</th>
        </tr>
      </thead>
      <tbody>
        {foreach $shipment['history'] as $historyDetail}
          <tr class="item">
            <td class="bold">
              <label for="cb_59">
                {$historyDetail['postnl_status']|intval nofilter}
              </label>
            </td>
            <td class="bold">
              <label for="cb_59">
                {$historyDetail['date_upd']|escape:'htmlall':'UTF-8' nofilter nofilter}
              </label>
            </td>
          </tr>
        {/foreach}
      </tbody>
    </table>
  {/foreach}
</div>

{*
 * 2017 DM Productions B.V.
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
 * @author     DM Productions B.V. <info@dmp.nl>
 * @author     Michael Dekker <info@mijnpresta.nl>
 * @copyright  2010-2017 DM Productions B.V.
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
	<td class="myparcel_align_right">
		<table class="myparcel_table">
			<tr>
				<td id="mypa_exist_{$id_order|escape:'htmlall':'UTF-8'}">
					<div>
						<input type="checkbox" {if isset($checks)}value="{$checks|escape:'htmlall':'UTF-8'}" {/if} id="mypa_check_{$id_order|escape:'htmlall':'UTF-8'}" class="mypaleft mypacheck">
						{if isset($items)}{$items|escape:'quotes':'UTF-8'}{/if}
					</div>
				</td>
				<td class="mypafunc">
					<a href="#" class="myparcel-consignment-new"><img src="/modules/myparcel/views/img/myparcel_pdf_add.png" alt="{l s='New' mod='myparcel'}"></a>
					<a href="#" class="myparcel-consignment-retour"><img src="/modules/myparcel/views/img/myparcel_retour_add.png" alt="{l s='Retour' mod='myparcel'}"></a>
				</td>
			</tr>
		</table>
	</td>
{else}
	<td class="pointer" id="mypa_exist_{$id_order|escape:'htmlall':'UTF-8'}">
		<div class="mypa_item">
			<div class="mypa_item_check">
				<input type="checkbox" {if isset($checks)}value="{$checks|escape:'htmlall':'UTF-8'}"
					   {/if}id="mypa_check_{$id_order|escape:'htmlall':'UTF-8'}" class="mypaleft mypacheck">
			</div>
			<div class="mypa_item_info">
				{if isset($items)}{$items|escape:'quotes':'UTF-8'}{/if}
			</div>
			<div class="mypa_item_btns">
				<a href="#myparcelExportModal" class="myparcel-consignment-new"><img src="/modules/myparcel/views/img/myparcel_pdf_add.png" alt="{l s='New' mod='myparcel'}"></a>
				<br/>
				<a href="#myparcelExportModal" class="myparcel-consignment-retour">
					<img src="/modules/myparcel/views/img/myparcel_retour_add.png"
						 alt="{l s='Retour' mod='myparcel'}">
				</a>
			</div>
		</div>
	</td>
{/if}

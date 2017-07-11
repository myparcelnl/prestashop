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
{extends file="helpers/list/list_content.tpl"}

{block name="td_content"}
	{if isset($params.prefix)}{$params.prefix|escape:'htmlall':'UTF-8'}{/if}
	{if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}<span class="badge badge-success">{/if}
{if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}
	<span class="badge badge-warning">{/if}
{if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}
	<span class="badge badge-danger">{/if}
{if isset($params.color) && isset($tr[$params.color])}
	<span class="label color_field"
		  style="background-color:{$tr[$params.color]|escape:'htmlall':'UTF-8'};color:{if Tools::getBrightness($tr[$params.color]) < 128}white{else}#383838{/if}">
{/if}
	{if isset($tr.$key)}
		{if isset($params.active)}
			{$tr.$key}
		{elseif isset($params.activeVisu)}
			{if $tr.$key}
				<i class="icon-check-ok"></i>
					 {l s='Enabled'}
						{else}
				<i class="icon-remove"></i>
				{l s='Disabled'}
			{/if}
					{elseif isset($params.position)}
						{if !$filters_has_value && $order_by == 'position' && $order_way != 'DESC'}
			<div class="dragGroup">
				<div class="positions">
					{($tr.$key.position + 1)|escape:'htmlall':'UTF-8'}
				</div>
			</div>
		{else}
			{($tr.$key.position + 1)|escape:'htmlall':'UTF-8'}
		{/if}
					{elseif isset($params.image)}
						{$tr.$key|escape:'htmlall':'UTF-8'}
					{elseif isset($params.icon)}
						{if is_array($tr[$key])}
			{if isset($tr[$key]['class'])}
				<i class="{$tr[$key]['class']|escape:'htmlall':'UTF-8'}"></i>

												{else}

				<img src="../img/admin/{$tr[$key]['src']|escape:'htmlall':'UTF-8'}" alt="{$tr[$key]['alt']|escape:'htmlall':'UTF-8'}" title="{$tr[$key]['alt']|escape:'htmlall':'UTF-8'}"/>
			{/if}
		{/if}
					{elseif isset($params.type) && $params.type == 'price'}
						{displayPrice price=$tr.$key}
					{elseif isset($params.float)}
						{$tr.$key}
					{elseif isset($params.type) && $params.type == 'date'}
						{dateFormat date=$tr.$key full=0}
					{elseif isset($params.type) && $params.type == 'datetime'}
						{dateFormat date=$tr.$key full=1}
					{elseif isset($params.type) && $params.type == 'decimal'}
						{$tr.$key|string_format:"%.2f"}
					{elseif isset($params.type) && $params.type == 'percent'}
						{$tr.$key} {l s='%'}
					{* If type is 'editable', an input is created *}
					{elseif isset($params.type) && $params.type == 'editable' && isset($tr.id)}

			<input type="text" name="{$key|escape:'htmlall':'UTF-8'}_{$tr.id|escape:'htmlall':'UTF-8'}" value="{$tr.$key|escape:'html':'UTF-8'}" class="{$key|escape:'htmlall':'UTF-8'}"/>

									{elseif isset($params.callback)}
						{if isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
			<span title="{$tr.$key}">{$tr.$key|truncate:$params.maxlength:'...'|escape:'htmlall':'UTF-8'}</span>
		{else}
			{$tr.$key}
		{/if}
					{elseif $key == 'color'}
						{if !is_array($tr.$key)}
			<div style="background-color: {$tr.$key};" class="attributes-color-container"></div>

										{else} {*TEXTURE*}

			<img src="{$tr.$key.texture|escape:'htmlall':'UTF-8'}" alt="{$tr.name|escape:'htmlall':'UTF-8'}" class="attributes-color-container"/>
		{/if}
					{elseif isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}

			<span title="{$tr.$key}">{$tr.$key|truncate:$params.maxlength:'...'}</span>
		{else}
			{$tr.$key}
		{/if}
	{else}
	{if $params.type == 'cutoff_times' && isset($tr['cutoff_times'][0])}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_monday value=$tr['cutoff_times'][0]}
		{/if}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_tuesday value=$tr['cutoff_times'][1]}
		{/if}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_wednesday value=$tr['cutoff_times'][2]}
		{/if}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_thursday value=$tr['cutoff_times'][3]}
		{/if}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_friday value=$tr['cutoff_times'][4]}
		{/if}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_saturday value=$tr['cutoff_times'][5]}
		{/if}
		{if isset($tr['cutoff_times'][0])}
			{assign var=cutoff_sunday value=$tr['cutoff_times'][6]}
		{/if}
		{if isset($cutoff_monday)}
			{if !$cutoff_monday['nodispatch'] && !$cutoff_monday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_monday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_monday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_monday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_monday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_monday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_monday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
		{if isset($cutoff_tuesday)}
			{if !$cutoff_tuesday['nodispatch'] && !$cutoff_tuesday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_tuesday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_tuesday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_tuesday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_tuesday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_tuesday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_tuesday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
		{if isset($cutoff_wednesday)}
			{if !$cutoff_wednesday['nodispatch'] && !$cutoff_wednesday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_wednesday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_wednesday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_wednesday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_wednesday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_wednesday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_wednesday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
		<br class="visible-sm visible-xs visible-md">
		{if isset($cutoff_thursday)}
			{if !$cutoff_thursday['nodispatch'] && !$cutoff_thursday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_thursday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_thursday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_thursday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_thursday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_thursday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_thursday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
		{if isset($cutoff_friday)}
			{if !$cutoff_friday['nodispatch'] && !$cutoff_friday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_friday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_friday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_friday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_friday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_friday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_friday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
		{if isset($cutoff_saturday)}
			{if !$cutoff_saturday['nodispatch'] && !$cutoff_saturday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_saturday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_saturday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_saturday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_saturday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_saturday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_saturday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
		<br class="visible-sm visible-xs visible-md">
		{if isset($cutoff_sunday)}
			{if !$cutoff_sunday['nodispatch'] && !$cutoff_sunday['exception']}
				<span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_sunday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_sunday['time']|escape:'htmlall':'UTF-8'}</span>
			{elseif $cutoff_sunday['nodispatch']}
				<span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_sunday['name']|escape:'htmlall':'UTF-8'}</span></span>
			{else}
				<span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_sunday['name']|escape:'htmlall':'UTF-8'}</span> {$cutoff_sunday['time']|escape:'htmlall':'UTF-8'}</span>
			{/if}
		{/if}
	{else}
		{block name="default_field"}--{/block}
	{/if}
	{/if}
	{if isset($params.suffix)}{$params.suffix|escape:'htmlall':'UTF-8'}{/if}
{if isset($params.color) && isset($tr.color)}
	</span>
{/if}
{if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}
	</span>{/if}
{if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}
	</span>{/if}
	{if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}</span>{/if}
{/block}

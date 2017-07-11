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
{extends file="helpers/form/form.tpl"}

{block name="input"}
	{if $input.type == 'desc'}
	{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
		<p class="description">{$input.text|escape:'htmlall':'UTF-8'}</p>
	{else}
		<div class="alert alert-info">{$input.text|escape:'htmlall':'UTF-8'}</div>
	{/if}
	{elseif $input.type == 'hr'}
	{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
		<hr style="display: block;-webkit-margin-before: 0.5em;-webkit-margin-after: 0.5em;
			-webkit-margin-start: auto;-webkit-margin-end: auto;border-style: inset;border-width: 1px;">
	{else}
		<hr>
	{/if}
	{elseif $input.type == 'br'}
	<br />
	{elseif $input.type == 'switch' && $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
		{foreach $input.values as $value}
			<input type="radio" name="{$input.name|escape:'htmlall':'UTF-8'}"
				   id="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}"
				   value="{$value.value|escape:'htmlall':'UTF-8'}"
				   {if $fields_value[$input.name] == $value.value}checked="checked"{/if}
					{if isset($input.disabled) && $input.disabled}disabled="disabled"{/if} />
			<label class="t" for="{$input.name|escape:'htmlall':'UTF-8'}_{$value.id|escape:'htmlall':'UTF-8'}">
				{if isset($input.is_bool) && $input.is_bool == true}
					{if $value.value == 1}
						<img src="../img/admin/enabled.gif" alt="{$value.label|escape:'htmlall':'UTF-8'}"
							 title="{$value.label|escape:'htmlall':'UTF-8'}" />
					{else}
						<img src="../img/admin/disabled.gif" alt="{$value.label|escape:'htmlall':'UTF-8'}"
							 title="{$value.label|escape:'htmlall':'UTF-8'}" />
					{/if}
				{else}
					{$value.label|escape:'htmlall':'UTF-8'}
				{/if}
			</label>
			{if isset($input.br) && $input.br}<br />{/if}
			{if isset($value.p) && $value.p}<p>{$value.p|escape:'htmlall':'UTF-8'}</p>{/if}
		{/foreach}
	{elseif $input.type == 'fontselect'}
		<div class="form-group">
			<label for="{$input.label|escape:'html':'UTF-8'}" class="control-label col-lg-3">
			<div class="col-lg-9">
				<input id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" type="hidden" value="{$fields_value[$input.name]|escape:'html':'UTF-8'}" />
			</div>
		</div>
		<script type="text/javascript">
			$(document).ready(function() {
				$('#{$input.name|escape:'javascript':'UTF-8'}').fontselect({
					{if isset($fields_value[$input.name])}placeholder: '{$fields_value[$input.name]|escape:'javascript':'UTF-8'}',{/if}
				})
					.change(function(){
						var font = $(this).val().replace(/\+/g, ' ');
						$('#{$input.name|escape:'javascript':'UTF-8'}').val(font);
					});
			});
		</script>
	{elseif $input.type == 'time'}
		{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}

		{else}
			<div class="row">
				<div class="input-group col-lg-2 col-md-3 col-sm-4">
					<input type="text"
						   id="{$input.name|escape:'html':'UTF-8'}"
						   name="{$input.name|escape:'html':'UTF-8'}"
						   class="{if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if}"
						   value="{$fields_value[$input.name]|escape:'html':'UTF-8'}"
							{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
							{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
							{if isset($input.required) && $input.required} required="required" {/if}
							{if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder|escape:'html':'UTF-8'}"{/if} />
					<span class="input-group-addon">
						<i class="icon-clock-o"></i>
					</span>
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function() {ldelim}
					$('#{$input.name|escape:'html':'UTF-8'}').timepicker({ldelim}
						timeOnly: true,
						timeFormat: 'hh:mm'
					{rdelim});
				{rdelim});
			</script>
		{/if}
	{elseif $input.type == 'cutoffexceptions'}
		<input type="hidden" id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}" value="{$fields_value[$input.name]|escape:'html':'UTF-8'}">
		<div class="row">
			<div id="datepicker_{$input.name|escape:'html':'UTF-8'}" class="col-lg-3"></div>
			<div class="col-lg-9">
				<br />
					<div class="row input-group clearfix"><div id="{$input.name|escape:'html':'UTF-8'}-nodispatch-btn" class="btn btn-default"><i class="icon-times"></i> {l s='No dispatch' mod='mppostnldeliveryopts'}</div></div>
				<br/>
				<div class="form-inline">
					<div class="form-group">
						<div class="input-group">
							<div id="{$input.name|escape:'html':'UTF-8'}-otherdispatch-btn" class="btn btn-default"><i class="icon-clock-o"></i> {l s='Different cut-off time' mod='mppostnldeliveryopts'}</div>
						</div>
						<div class="input-group">
							<input type="text"
								   id="{$input.name|escape:'html':'UTF-8'}-cutoff"
								   name="{$input.name|escape:'html':'UTF-8'}-cutoff"
								   class="{if isset($input.class)}{$input.class|escape:'html':'UTF-8'}{/if} form-control"
									{if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
									{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
									{if isset($input.required) && $input.required} required="required" {/if}
									{if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder|escape:'html':'UTF-8'}"{/if} />
						<span class="input-group-addon">
							<i class="icon-clock-o"></i>
						</span>
						</div>
					</div>
				</div>
				<br/>
				<div class="row input-group clearfix"><div id="{$input.name|escape:'html':'UTF-8'}-dispatch-btn" class="btn btn-success"><i class="icon-check"></i> {l s='Normal cut-off time' mod='mppostnldeliveryopts'}</div></div>
				<br/>
			</div>
		</div>
		<script type="text/javascript">
			function {$input.name|escape:'html':'UTF-8'}{literal}highlightDays(date) {
				var dates = JSON.parse($('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val());
				for (var i = 0; i < Object.keys(dates).length; i++) {
					var item = dates[Object.keys(dates)[i]];
					var formattedDate = Object.keys(dates)[i].split('-');
					if (new Date(formattedDate[2], formattedDate[1] - 1, formattedDate[0]).toISOString().slice(0, 10) == date.toISOString().slice(0, 10)) {
						if (item.cutoff) {
							return [true, 'ui-state-warning', ''];
						} else {
							return [true, 'ui-state-danger', ''];
						}

					}
				}
				return [true, ''];
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}dateSelect(date) {
				var dates = JSON.parse($('{/literal}#{$input.name|escape:'html':'UTF-8'}{literal}').val());
				if (!!dates[date]) {
					var item = dates[date];
					if (item.cutoff) {
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setOtherDispatch(item.cutoff);
					} else {
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setNoDispatch();
					}
				} else {
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setDispatch();
				}
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setDispatch() {
				$('#{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}-nodispatch-btn').addClass('btn-default').removeClass('btn-danger');
				$('#{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}-otherdispatch-btn').addClass('btn-default').removeClass('btn-warning');
				$('#{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}-dispatch-btn').addClass('btn-success').removeClass('btn-default');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val('');
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setOtherDispatch(cutoff) {
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-nodispatch-btn').addClass('btn-default').removeClass('btn-danger');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-otherdispatch-btn').addClass('btn-warning').removeClass('btn-default');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-dispatch-btn').addClass('btn-default').removeClass('btn-success');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val(cutoff);
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setNoDispatch() {
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-nodispatch-btn').addClass('btn-danger').removeClass('btn-default');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-otherdispatch-btn').addClass('btn-default').removeClass('btn-warning');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-dispatch-btn').addClass('btn-default').removeClass('btn-success');
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val('');
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}addDate(date) {
				var dates = JSON.parse($('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val());
				dates[date] = {
					"nodispatch": true
				};
				$('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val(JSON.stringify(dates));
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}addCutOff(date, cutoff) {
				var dates = JSON.parse($('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val());
				dates[date] = {
					"nodispatch": true,
					"cutoff": cutoff
				};
				$('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val(JSON.stringify(dates));
			}
			function {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}removeDate(date) {
				var dates = JSON.parse($('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val());
				delete dates[date];
				$('#{/literal}{$input.name|escape:'html':'UTF-8'}{literal}').val(JSON.stringify(dates));
			}
			$(document).ready(function () {
				$('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').datepicker({
					dateFormat: 'dd-mm-yy',
					beforeShowDay: {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}highlightDays,
					minDate: 0,
					onSelect: {/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}dateSelect
				});
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').timepicker({
					timeOnly: true,
					timeFormat: 'hh:mm'
				});
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-dispatch-btn').click(function () {
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}removeDate($('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val());
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setDispatch();
				});
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-otherdispatch-btn').click(function () {
					if ($('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val()) {
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}removeDate($('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val());
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}addCutOff(
								$('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val(),
								$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val()
						);
					}
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setOtherDispatch($('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val());
				});
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').change(function () {
					if ($(this).val()) {
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}removeDate($('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val());
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}addCutOff(
								$('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val(),
								$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-cutoff').val()
						);
						{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setOtherDispatch($(this).val());
					}
				});
				$('{/literal}#{$input.name|escape:'javascript':'UTF-8'}{literal}-nodispatch-btn').click(function () {
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}removeDate($('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val());
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}addDate($('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').val());
					{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}setNoDispatch();
				});
				var current_date = new Date($('{/literal}#datepicker_{$input.name|escape:'javascript':'UTF-8'}{literal}').datepicker('getDate')),
						yr      = current_date.getFullYear(),
						month   = (current_date.getMonth() + 1) < 10 ? '0' + (current_date.getMonth() + 1) : (current_date.getMonth() + 1),
						day     = current_date.getDate()  < 10 ? '0' + current_date.getDate()  : current_date.getDate(),
						new_current_date = day + '-' + month + '-' + yr;
				{/literal}{$input.name|escape:'javascript':'UTF-8'}{literal}dateSelect(new_current_date);
			});
			{/literal}
		</script>
	{else}
		{$smarty.block.parent}
	{/if}
{/block}

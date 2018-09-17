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
{extends file="helpers/form/form.tpl"}

{block name="label"}
  {if isset($input.label)}
    <label class="control-label col-lg-3{if isset($input.required) && $input.required && $input.type != 'radio'} required{/if}{if !empty($input.hidden)} myparcel-dev-always-hidden{/if}">
      {if isset($input.hint)}
      <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{if is_array($input.hint)}
            {foreach $input.hint as $hint}
              {if is_array($hint)}
                {$hint.text|escape:'quotes'}
              {else}
                {$hint|escape:'quotes'}
              {/if}
            {/foreach}
          {else}
            {$input.hint|escape:'quotes'}
          {/if}">
      {/if}
        {$input.label}
        {if isset($input.hint)}
      </span>
      {/if}
    </label>
  {/if}
{/block}

{block name="input"}
  {if $input.type == 'br'}
    <br>
  {elseif $input.type == 'text' || $input.type == 'tags'}
    {if isset($input.lang) AND $input.lang}
    {if $languages|count > 1}
      <div class="form-group{if !empty($input.hidden)} myparcel-dev-always-hidden{/if}">
        {/if}
        {foreach $languages as $language}
          {assign var='value_text' value=$fields_value[$input.name][$language.id_lang]}
          {if $languages|count > 1}
            <div class="translatable-field lang-{$language.id_lang}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
            <div class="col-lg-9">
          {/if}
          {if $input.type == 'tags'}
          {literal}
            <script type="text/javascript">
              $().ready(function () {
                var input_id = '{/literal}{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}{literal}';
                $('#'+input_id).tagify({delimiters: {/literal}{if isset($input.delimiters)}[{$input.delimiters}]{else}[13,44]{/if}{literal}, addTagPrompt: '{/literal}{if isset($input.tagPrompt)}{$input.tagPrompt|escape:'javascript'}{l s='Add tag' js=1}{/if}{literal}'});
                $({/literal}'#{$table}{literal}_form').submit( function() {
                  $(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
                });
              });
            </script>
          {/literal}
          {/if}
        {if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
          <div class="input-group{if isset($input.class)} {$input.class}{/if}">
        {/if}
          {if isset($input.disableKeys) && is_array($input.disableKeys)}
            <script type="text/javascript">
              $().ready(function() {
                $('#{if isset($input.id)}{$input.id|escape:'javascript'}_{$language.id_lang|intval}{else}{$input.name|escape:'javascript'}_{$language.id_lang|intval}{/if}').keydown(function (e) {
                  {foreach $input.disableKeys AS $disabledKey}
                  if (e.which == {$disabledKey|intval}) {
                    return false;
                  }
                  {/foreach}
                });
              });
            </script>
          {/if}
          {if isset($input.maxchar) && $input.maxchar}
            <span id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}_counter" class="input-group-addon">
              <span class="text-count-down">{$input.maxchar|intval}</span>
            </span>
          {/if}
          {if isset($input.prefix)}
            <span class="input-group-addon">
              {$input.prefix}
            </span>
          {/if}
          <input type="text"
                 id="{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"
                 name="{$input.name}_{$language.id_lang}"
                 class="{if isset($input.class)}{$input.class}{/if}{if $input.type == 'tags'} tagify{/if}"
                 value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'html'}{else}{$value_text|escape:'html'}{/if}"
                 onkeyup="if (isArrowKey(event)) return ;updateFriendlyURL();"
                  {if isset($input.size)} size="{$input.size}"{/if}
                  {if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
                  {if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
                  {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                  {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                  {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
                  {if isset($input.required) && $input.required} required="required" {/if}
                  {if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder}"{/if} />
          {if isset($input.suffix)}
            <span class="input-group-addon">
              {$input.suffix}
            </span>
          {/if}
        {if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
          </div>
        {/if}
          {if $languages|count > 1}
            </div>
            <div class="col-lg-2">
              <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                {$language.iso_code}
                <i class="icon-caret-down"></i>
              </button>
              <ul class="dropdown-menu">
                {foreach from=$languages item=language}
                  <li><a href="javascript:hideOtherLanguage({$language.id_lang});" tabindex="-1">{$language.name}</a></li>
                {/foreach}
              </ul>
            </div>
            </div>
          {/if}
        {/foreach}
        {if isset($input.maxchar) && $input.maxchar}
          <script type="text/javascript">
            $(document).ready(function(){
              {foreach from=$languages item=language}
              countDown($("#{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}"), $("#{if isset($input.id)}{$input.id}_{$language.id_lang}{else}{$input.name}_{$language.id_lang}{/if}_counter"));
              {/foreach}
            });
          </script>
        {/if}
        {if $languages|count > 1}
      </div>
    {/if}
    {else}
      {if $input.type == 'tags'}
      {literal}
        <script type="text/javascript">
          $().ready(function () {
            var input_id = '{/literal}{if isset($input.id)}{$input.id}{else}{$input.name}{/if}{literal}';
            $('#'+input_id).tagify({delimiters: {/literal}{if isset($input.delimiters)}[{$input.delimiters}]{else}[13,44]{/if}{literal}, addTagPrompt: '{/literal}{if isset($input.tagPrompt)}{$input.tagPrompt|escape:'javascript'}{else}{l s='Add tag'}{/if}{literal}'});
            $({/literal}'#{$table}{literal}_form').submit( function() {
              $(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
            });
          });
        </script>
      {/literal}
      {/if}
      {assign var='value_text' value=$fields_value[$input.name]}
    {if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
      <div class="input-group{if isset($input.class)} {$input.class}{/if}">
        {/if}
        {if isset($input.maxchar) && $input.maxchar}
          <span id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}_counter" class="input-group-addon"><span class="text-count-down">{$input.maxchar|intval}</span></span>
        {/if}
        {if isset($input.prefix)}
          <span class="input-group-addon">
            {$input.prefix}
          </span>
        {/if}
        <input type="text"
               name="{$input.name}"
               id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
               value="{if isset($input.string_format) && $input.string_format}{$value_text|string_format:$input.string_format|escape:'html'}{else}{$value_text|escape:'html'}{/if}"
               class="{if isset($input.class)}{$input.class}{/if}{if $input.type == 'tags'} tagify{/if}"
                {if isset($input.size)} size="{$input.size}"{/if}
                {if isset($input.maxchar) && $input.maxchar} data-maxchar="{$input.maxchar|intval}"{/if}
                {if isset($input.maxlength) && $input.maxlength} maxlength="{$input.maxlength|intval}"{/if}
                {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                {if isset($input.autocomplete) && !$input.autocomplete} autocomplete="off"{/if}
                {if isset($input.required) && $input.required } required="required" {/if}
                {if isset($input.placeholder) && $input.placeholder } placeholder="{$input.placeholder}"{/if}
        />
        {if isset($input.suffix)}
          <span class="input-group-addon">
            {$input.suffix}
          </span>
        {/if}
        {if isset($input.disableKeys) && is_array($input.disableKeys)}
          <script type="text/javascript">
            $().ready(function() {
              $('#{if isset($input.id)}{$input.id|escape:'javascript'}_{$language.id_lang|intval}{else}{$input.name|escape:'javascript'}_{$language.id_lang|intval}{/if}').keypress(function (e) {
                {foreach $input.disableKeys AS $disabledKey}
                if (e.which == {$disabledKey|intval}) {
                  return false;
                }
                {/foreach}
              });
            });
          </script>
        {/if}
        {if isset($input.maxchar) || isset($input.prefix) || isset($input.suffix)}
      </div>
    {/if}
      {if isset($input.maxchar) && $input.maxchar}
        <script type="text/javascript">
          $(document).ready(function(){
            countDown($("#{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"), $("#{if isset($input.id)}{$input.id}{else}{$input.name}{/if}_counter"));
          });
        </script>
      {/if}
    {/if}
  {elseif $input.type == 'switch'}
    <span class="switch prestashop-switch fixed-width-lg{if !empty($input.hidden)} myparcel-dev-always-hidden{/if}">
      {foreach $input.values as $value}
        <input type="radio" name="{$input.name}"{if $value.value == 1} id="{$input.name}_on"{else} id="{$input.name}_off"{/if} value="{$value.value}"{if $fields_value[$input.name] == $value.value} checked="checked"{/if}{if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}/>
      {strip}
        <label {if $value.value == 1} for="{$input.name}_on"{else} for="{$input.name}_off"{/if}>
        {if $value.value == 1}
          {l s='Yes'}
        {else}
          {l s='No'}
        {/if}
      </label>
      {/strip}
      {/foreach}
      <a class="slide-button btn"></a>
    </span>
  {elseif $input.type == 'fontselect'}
    <div class="form-group">
      <label id="label-{$input.name|escape:'html'}"
             for="{$input.label|escape:'html'}"
             class="control-label fixed-width-xxl"
             style="margin-left: 5px"
      >
        <select id="{$input.name|escape:'html'}"
                name="{$input.name|escape:'html'}"
        >
          <option value="{$fields_value[$input.name]|escape:'javascript'}"
                  label="{$fields_value[$input.name]|escape:'javascript'}"
                  selected="selected"
          >
            {$fields_value[$input.name]|escape:'javascript'}
          </option>
        </select>
      </label>
    </div>
    <script type="text/javascript">
      (function () {
        function ready(fn) {
          if (document.readyState !== 'loading'){
            fn();
          } else if (document.addEventListener) {
            document.addEventListener('DOMContentLoaded', fn);
          } else {
            document.attachEvent('onreadystatechange', function() {
              if (document.readyState !== 'loading')
                fn();
            });
          }
        }

        function initFontselect() {
          if (typeof window.Fontselect === 'undefined') {
            setTimeout(initFontselect, 100);

            return;
          }

          window.myparcelFontselect = window.stripeFontselect || { };
          window.myparcelFontselect.checkout = new window.Fontselect('{$input.name|escape:'html'}', {
            {if isset($fields_value[$input.name])}placeholder: '{$fields_value[$input.name]|escape:'javascript'}',{/if}
          });
        }

        ready(initFontselect);
      }());
    </script>
  {elseif $input.type == 'checkout'}
    {if !Module::isEnabled('myparcel')}
      {l s='Preview not available. Module has been disabled.' mod='myparcel'}
    {else}
      <iframe id="checkoutexample"
              style="border: 1px solid #C7D6DB; border-radius: 3px; padding: 0"
              class="col-xs-12 col-sm-12 col-md-9"
              height="620"
              src="{$smarty.server.REQUEST_URI|escape:'html'}&demo=1"
              frameborder="0"
      ></iframe>
      <script type="text/javascript">
        (function () {
          var currentStyle = null;

          function ready(fn) {
            if (document.readyState !== 'loading'){
              fn();
            } else if (document.addEventListener) {
              window.addEventListener('DOMContentLoaded', fn);
            } else {
              document.attachEvent('onreadystatechange', function() {
                if (document.readyState !== 'loading')
                  fn();
              });
            }
          }

          function sendStyle() {
            var iframeWindow = document.getElementById('checkoutexample').contentWindow;

            var style = {
              foreground1Color: document.querySelector('[name={MyParcel::CHECKOUT_FG_COLOR1|escape:'htmlall'}]').value,
              foreground2Color: document.querySelector('[name={MyParcel::CHECKOUT_FG_COLOR2|escape:'htmlall'}]').value,
              foreground3Color: document.querySelector('[name={MyParcel::CHECKOUT_FG_COLOR3|escape:'htmlall'}]').value,
              background1Color: document.querySelector('[name={MyParcel::CHECKOUT_BG_COLOR1|escape:'htmlall'}]').value,
              background2Color: document.querySelector('[name={MyParcel::CHECKOUT_BG_COLOR2|escape:'htmlall'}]').value,
              background3Color: document.querySelector('[name={MyParcel::CHECKOUT_BG_COLOR3|escape:'htmlall'}]').value,
              highlightColor: document.querySelector('[name={MyParcel::CHECKOUT_HL_COLOR|escape:'htmlall'}]').value,
              inactiveColor: document.querySelector('[name={MyParcel::CHECKOUT_INACTIVE_COLOR|escape:'htmlall'}]').value,
              fontFamily: document.querySelector('[name={MyParcel::CHECKOUT_FONT|escape:'htmlall'}]').value,
              fontSize: document.querySelector('[name={MyParcel::CHECKOUT_FONT_SIZE|escape:'htmlall'}]').value,
            };

            if (JSON.stringify(currentStyle) === JSON.stringify(style)) {
              return;
            }

            var newEvent = {
              subject: 'sendStyle',
              style: style
            };
            iframeWindow.postMessage(JSON.stringify(newEvent), window.location.href);
          }

          function receiveStyle(event) {
            var originLink = document.createElement('a');
            originLink.href = event.origin;
            var currentLink = document.createElement('a');
            currentLink.href = window.location.href;

            if (!event.data) {
              return;
            }

            try {
              var data = JSON.parse(event.data);
            } catch (e) {
              return;
            }

            if (originLink.host === currentLink.host
              && typeof data === 'object'
              && data.subject === 'receivedStyle'
            ) {
              currentStyle = data.style;
            }
          }

          ready(function() {
            {* Send the style every 100ms when it has changed *}
            {* Request an update from the target frame to verify the style has been applied *}
            window.addEventListener('message', receiveStyle, false);

            setInterval(sendStyle, 100);
          });
        }());
      </script>
    {/if}
  {elseif $input.type == 'paperselector'}
    <input type="hidden" name="{$input.name|escape:'htmlall'}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'htmlall'}{/if}">
    <div id="paper-selector"></div>
    <style>
      .print-overlay .paper {
        float: left!important;
        padding-left: 8px!important;
      }
      .print-overlay i {
        left: 36px!important;
      }
    </style>
    <script type="text/javascript">
      (function () {
        var selection = {
          size: 'standard',
          labels: {
            1: true,
            2: true,
            3: true,
            4: true,
          }
        };

        function paperSelector() {
          if (typeof window.MyParcelModule === 'undefined'
            || typeof window.MyParcelModule.paperselector === 'undefined'
            || typeof window.MyParcelModule.paperselector.default === 'undefined'
          ) {
            setTimeout(paperSelector, 100);

            return;
          }

          try {
            var fromInput = document.querySelector('input[name={$input.name|escape:'htmlall'}]').value;
            fromInput = JSON.parse(fromInput);
            if (fromInput.size && fromInput.labels) {
              selection = fromInput;
            }
          } catch (e) {
          }

          function setInput() {
            document.querySelector('input[name={$input.name|escape:'htmlall'}]').value = JSON.stringify(selection);
          }

          function changeSize(size) {
            selection.size = size;
            setInput();
          }

          function changeLabels(labels) {
            selection.labels = labels;
            setInput();
          }

          new window.MyParcelModule.paperselector.default({
            selected: selection,
            onChangeSize: changeSize,
            onChangeLabels: changeLabels,
          }, {include file="../../../translations.tpl"});
        }

        paperSelector();
      }());
    </script>
  {elseif $input.type == 'time'}
    <div class="row">
      <div class="input-group col-lg-2 col-md-3 col-sm-4">
        <input type="text"
               id="{$input.name|escape:'html'}"
               name="{$input.name|escape:'html'}"
               class="{if isset($input.class)}{$input.class|escape:'html'}{/if}"
               value="{$fields_value[$input.name]|escape:'html'}"
                {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                {if isset($input.required) && $input.required} required="required" {/if}
                {if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder|escape:'html'}"{/if} />
        <span class="input-group-addon">
          <i class="icon-clock-o"></i>
        </span>
      </div>
    </div>
    <script type="text/javascript">
      (function () {
        function initTimepicker() {
          if (typeof $ === 'undefined') {
            setTimeout(initTimePicker, 100);

            return;
          }
        }

        $(document).ready(function () {ldelim}
          $('#{$input.name|escape:'html'}').timepicker({ldelim}
            timeOnly: true,
            timeFormat: 'hh:mm'
            {rdelim});
          {rdelim});

        initTimepicker();
      }());
    </script>
  {elseif $input.type == 'cutoffexceptions'}
    <input type="hidden" id="{$input.name|escape:'html'}" name="{$input.name|escape:'html'}"
           value="{$fields_value[$input.name]|escape:'html'}">
    <div class="row">
      <div id="datepicker_{$input.name|escape:'html'}" class="col-lg-3" style="margin-bottom: 5px"></div>
      <div class="col-lg-9 clearfix">
        <div id="{$input.name|escape:'html'}_datepanel" class="panel">
          <div class="panel-heading">
            <i class="icon icon-calendar"></i> <span id="{$input.name|escape:'html'}_datetitle"></span>
          </div>
          <div class="date-warning" style="display:none">
            <div class="alert alert-info">{l s='Select a date in the future to configure its cutoff time' mod='myparcel'}</div>
          </div>
          <div class="panel-body">
            <div class="btn-group" role="group">
              <button type="button" id="{$input.name|escape:'html'}-nodispatch-btn" class="btn btn-default">
                <i class="icon-times"></i> {l s='No dispatch' mod='myparcel'}
              </button>
              <button type="button" id="{$input.name|escape:'html'}-otherdispatch-btn" class="btn btn-default">
                <i  class="icon-clock-o"></i> {l s='Different cut-off time' mod='myparcel'}
              </button>
              <div id="{$input.name|escape:'html'}-dispatch-btn" class="btn btn-success">
                <i class="icon-check"></i> {l s='Normal cut-off time' mod='myparcel'}
              </div>
            </div>
            <div class="form-inline well" style="margin-top: 5px">
              <div class="form-group">
                <label for="{$input.name|escape:'html'}-cutoff">{l s='Cut-off time' mod='myparcel'}: </label>
                <div class="input-group">
                  <input type="text"
                         id="{$input.name|escape:'html'}-cutoff"
                         name="{$input.name|escape:'html'}-cutoff"
                         class="{if isset($input.class)}{$input.class|escape:'html'}{/if} form-control"
                          {if isset($input.readonly) && $input.readonly} readonly="readonly"{/if}
                          {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}
                          {if isset($input.required) && $input.required} required="required" {/if}
                          {if isset($input.placeholder) && $input.placeholder} placeholder="{$input.placeholder|escape:'html'}"{/if} />
                  <span class="input-group-addon">
                  <i class="icon-clock-o"></i>
                </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      (function () {
        function {$input.name|escape:'html'}highlightDays(date) {
          var dates = JSON.parse($('#{$input.name|escape:'html'}').val());
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

        function {$input.name|escape:'javascript'}dateSelect(date) {
          console.log(date);
          if (moment().format('YYYY-MM-DD') > moment(date, 'DD-MM-YYYY').format('YYYY-MM-DD')) {
            $('#{$input.name|escape:'html'}_datepanel').find('.panel-body').hide();
            $('#{$input.name|escape:'html'}_datepanel').find('.date-warning').show();
          } else {
            $('#{$input.name|escape:'html'}_datepanel').find('.panel-body').show();
            $('#{$input.name|escape:'html'}_datepanel').find('.date-warning').hide();
            var dates = JSON.parse($('#{$input.name|escape:'html'}').val());
            if (!!dates[date]) {
              var item = dates[date];
              if (item.cutoff) {
                {$input.name|escape:'javascript'}setOtherDispatch(item.cutoff);
              } else {
                {$input.name|escape:'javascript'}setNoDispatch();
              }
            } else {
              {$input.name|escape:'javascript'}setDispatch();
            }
          }

          $('#{$input.name|escape:'html'}_datetitle').text(moment(date, 'DD-MM-YYYY').format('DD MMMM YYYY'));
        }

        window.setDate = {$input.name|escape:'javascript'}dateSelect;

        function {$input.name|escape:'javascript'}setDispatch() {
          $('#{$input.name|escape:'javascript'}-nodispatch-btn').addClass('btn-default').removeClass('btn-danger');
          $('#{$input.name|escape:'javascript'}-otherdispatch-btn').addClass('btn-default').removeClass('btn-warning');
          $('#{$input.name|escape:'javascript'}-dispatch-btn').addClass('btn-success').removeClass('btn-default');
          $('#{$input.name|escape:'javascript'}-cutoff').val('');
        }

        function {$input.name|escape:'javascript'}setOtherDispatch(cutoff) {
          $('#{$input.name|escape:'javascript'}-nodispatch-btn').addClass('btn-default').removeClass('btn-danger');
          $('#{$input.name|escape:'javascript'}-otherdispatch-btn').addClass('btn-warning').removeClass('btn-default');
          $('#{$input.name|escape:'javascript'}-dispatch-btn').addClass('btn-default').removeClass('btn-success');
          $('#{$input.name|escape:'javascript'}-cutoff').val(cutoff);
        }

        function {$input.name|escape:'javascript'}setNoDispatch() {

          $('#{$input.name|escape:'javascript'}-nodispatch-btn').addClass('btn-danger').removeClass('btn-default');
          $('#{$input.name|escape:'javascript'}-otherdispatch-btn').addClass('btn-default').removeClass('btn-warning');
          $('#{$input.name|escape:'javascript'}-dispatch-btn').addClass('btn-default').removeClass('btn-success');
          $('#{$input.name|escape:'javascript'}-cutoff').val('');
        }

        function {$input.name|escape:'javascript'}addDate(date) {
          var dates = JSON.parse($('#{$input.name|escape:'html'}').val());
          dates[date] = {
            "nodispatch": true
          };
          $('#{$input.name|escape:'html'}').val(JSON.stringify(dates));
        }

        function {$input.name|escape:'javascript'}addCutOff(date, cutoff) {
          var dates = JSON.parse($('#{$input.name|escape:'html'}').val());
          dates[date] = {
            "nodispatch": true,
            "cutoff": cutoff
          };
          $('#{$input.name|escape:'html'}').val(JSON.stringify(dates));
        }

        function {$input.name|escape:'javascript'}removeDate(date) {
          var dates = JSON.parse($('#{$input.name|escape:'html'}').val());
          delete dates[date];
          $('#{$input.name|escape:'html'}').val(JSON.stringify(dates));
        }

        $(document).ready(function () {
          $('#datepicker_{$input.name|escape:'javascript'}').datepicker({
            dateFormat: 'dd-mm-yy',
            beforeShowDay: {$input.name|escape:'javascript'}highlightDays,
            minDate: 0,
            onSelect: {$input.name|escape:'javascript'}dateSelect
          });
          $('#{$input.name|escape:'javascript'}-cutoff').timepicker({
            timeOnly: true,
            timeFormat: 'hh:mm'
          });
          $('#{$input.name|escape:'javascript'}-dispatch-btn').click(function () {
            {$input.name|escape:'javascript'}removeDate($('#datepicker_{$input.name|escape:'javascript'}').val());
            {$input.name|escape:'javascript'}setDispatch();
          });
          $('#{$input.name|escape:'javascript'}-otherdispatch-btn').click(function () {
            if ($('#{$input.name|escape:'javascript'}-cutoff').val()) {
              {$input.name|escape:'javascript'}removeDate($('#datepicker_{$input.name|escape:'javascript'}').val());
              {$input.name|escape:'javascript'}addCutOff(
                $('#datepicker_{$input.name|escape:'javascript'}').val(),
                $('#{$input.name|escape:'javascript'}-cutoff').val()
              );
            }
            {$input.name|escape:'javascript'}setOtherDispatch($('#{$input.name|escape:'javascript'}-cutoff').val());
          });
          $('#{$input.name|escape:'javascript'}-cutoff').change(function () {
            if ($(this).val()) {
              {$input.name|escape:'javascript'}removeDate($('#datepicker_{$input.name|escape:'javascript'}').val());
              {$input.name|escape:'javascript'}addCutOff(
                $('#datepicker_{$input.name|escape:'javascript'}').val(),
                $('#{$input.name|escape:'javascript'}-cutoff').val()
              );
              {$input.name|escape:'javascript'}setOtherDispatch($(this).val());
            }
          });
          $('#{$input.name|escape:'javascript'}-nodispatch-btn').click(function () {
            {$input.name|escape:'javascript'}removeDate($('#datepicker_{$input.name|escape:'javascript'}').val());
            {$input.name|escape:'javascript'}addDate($('#datepicker_{$input.name|escape:'javascript'}').val());
            {$input.name|escape:'javascript'}setNoDispatch();
          });
          var current_date = new Date($('#datepicker_{$input.name|escape:'javascript'}').datepicker('getDate')),
            yr = current_date.getFullYear(),
            month = (current_date.getMonth() + 1) < 10 ? '0' + (current_date.getMonth() + 1) : (current_date.getMonth() + 1),
            day = current_date.getDate() < 10 ? '0' + current_date.getDate() : current_date.getDate(),
            new_current_date = day + '-' + month + '-' + yr;
          {$input.name|escape:'javascript'}dateSelect(new_current_date);
        });
      }());
    </script>
  {else}
    {$smarty.block.parent}
  {/if}
{/block}

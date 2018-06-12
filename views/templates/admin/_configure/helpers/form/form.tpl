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

{block name="input"}
  {if $input.type == 'br'}
    <br/>
  {elseif $input.type == 'fontselect'}
    <div class="form-group">
      <label id="label-{$input.name|escape:'html':'UTF-8'}"
             for="{$input.label|escape:'html':'UTF-8'}"
             class="control-label fixed-width-xxl"
             style="margin-left: 5px"
      >
        <select id="{$input.name|escape:'html':'UTF-8'}"
                name="{$input.name|escape:'html':'UTF-8'}"
        >
          <option value="{$fields_value[$input.name]|escape:'javascript':'UTF-8'}"
                  label="{$fields_value[$input.name]|escape:'javascript':'UTF-8'}"
                  selected="selected"
          >
            {$fields_value[$input.name]|escape:'javascript':'UTF-8'}
          </option>
        </select>
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

          window.stripeFontselect = window.stripeFontselect || { };
          window.stripeFontselect.checkout = new window.Fontselect('{$input.name|escape:'html':'UTF-8'}', {
            {if isset($fields_value[$input.name])}placeholder: '{$fields_value[$input.name]|escape:'javascript':'UTF-8'}',{/if}
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
              frameborder="0"
              class="col-xs-12 col-sm-12 col-md-9"
              height="580"
              src="../index.php?fc=module&module=myparcel&controller=myparcelcheckoutdemo"
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
              foreground1Color: document.querySelector('[name={MyParcel::CHECKOUT_FG_COLOR1|escape:'htmlall':'UTF-8'}]').value,
              foreground2Color: document.querySelector('[name={MyParcel::CHECKOUT_FG_COLOR2|escape:'htmlall':'UTF-8'}]').value,
              background1Color: document.querySelector('[name={MyParcel::CHECKOUT_BG_COLOR1|escape:'htmlall':'UTF-8'}]').value,
              background2Color: document.querySelector('[name={MyParcel::CHECKOUT_BG_COLOR2|escape:'htmlall':'UTF-8'}]').value,
              background3Color: document.querySelector('[name={MyParcel::CHECKOUT_BG_COLOR3|escape:'htmlall':'UTF-8'}]').value,
              highlightColor: document.querySelector('[name={MyParcel::CHECKOUT_HL_COLOR|escape:'htmlall':'UTF-8'}]').value,
              fontFamily: document.querySelector('[name={MyParcel::CHECKOUT_FONT|escape:'htmlall':'UTF-8'}]').value,
              fontSize: document.querySelector('[name={MyParcel::CHECKOUT_FONT_SIZE|escape:'htmlall':'UTF-8'}]').value,
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
    <input type="hidden" name="{$input.name|escape:'htmlall':'UTF-8'}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]|escape:'htmlall':'UTF-8'}{/if}">
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
          ) {
            setTimeout(paperSelector, 100);

            return;
          }

          try {
            var fromInput = document.querySelector('input[name={$input.name|escape:'htmlall':'UTF-8'}]').value;
            fromInput = JSON.parse(fromInput);
            if (fromInput.size && fromInput.labels) {
              selection = fromInput;
            }
          } catch (e) {
          }

          function setInput() {
            document.querySelector('input[name={$input.name|escape:'htmlall':'UTF-8'}]').value = JSON.stringify(selection);
          }

          function changeSize(size) {
            selection.size = size;
            setInput();
          }

          function changeLabels(labels) {
            selection.labels = labels;
            setInput();
          }

          new window.MyParcelModule.paperselector({
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
      (function () {
        function initTimepicker() {
          if (typeof $ === 'undefined') {
            setTimeout(initTimePicker, 100);

            return;
          }
        }

        $(document).ready(function () {ldelim}
          $('#{$input.name|escape:'html':'UTF-8'}').timepicker({ldelim}
            timeOnly: true,
            timeFormat: 'hh:mm'
            {rdelim});
          {rdelim});

        initTimepicker();
      }());
    </script>
  {elseif $input.type == 'cutoffexceptions'}
    <input type="hidden" id="{$input.name|escape:'html':'UTF-8'}" name="{$input.name|escape:'html':'UTF-8'}"
           value="{$fields_value[$input.name]|escape:'html':'UTF-8'}">
    <div class="row">
      <div id="datepicker_{$input.name|escape:'html':'UTF-8'}" class="col-lg-3" style="margin-bottom: 5px"></div>
      <div class="col-lg-9 clearfix">
        <div id="{$input.name|escape:'html':'UTF-8'}_datepanel" class="panel">
          <div class="panel-heading">
            <i class="icon icon-calendar"></i> <span id="{$input.name|escape:'html':'UTF-8'}_datetitle"></span>
          </div>
          <div class="date-warning" style="display:none">
            <div class="alert alert-info">{l s='Select a date in the future to configure its cutoff time' mod='myparcel'}</div>
          </div>
          <div class="panel-body">
            <div class="btn-group" role="group">
              <button type="button" id="{$input.name|escape:'html':'UTF-8'}-nodispatch-btn" class="btn btn-default">
                <i class="icon-times"></i> {l s='No dispatch' mod='myparcel'}
              </button>
              <button type="button" id="{$input.name|escape:'html':'UTF-8'}-otherdispatch-btn" class="btn btn-default">
                <i  class="icon-clock-o"></i> {l s='Different cut-off time' mod='myparcel'}
              </button>
              <div id="{$input.name|escape:'html':'UTF-8'}-dispatch-btn" class="btn btn-success">
                <i class="icon-check"></i> {l s='Normal cut-off time' mod='myparcel'}
              </div>
            </div>
            <div class="form-inline well" style="margin-top: 5px">
              <div class="form-group">
                <label for="{$input.name|escape:'html':'UTF-8'}-cutoff">{l s='Cut-off time' mod='myparcel'}: </label>
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
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      (function () {
        function {$input.name|escape:'html':'UTF-8'}highlightDays(date) {
          var dates = JSON.parse($('#{$input.name|escape:'html':'UTF-8'}').val());
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

        function {$input.name|escape:'javascript':'UTF-8'}dateSelect(date) {
          if (moment().format('DD-MM-YYYY') === moment(date, 'DD-MM-YYYY').format('DD-MM-YYYY')) {
            $('#{$input.name|escape:'html':'UTF-8'}_datepanel').find('.panel-body').hide();
            $('#{$input.name|escape:'html':'UTF-8'}_datepanel').find('.date-warning').show();
          } else {
            $('#{$input.name|escape:'html':'UTF-8'}_datepanel').find('.panel-body').show();
            $('#{$input.name|escape:'html':'UTF-8'}_datepanel').find('.date-warning').hide();
            var dates = JSON.parse($('#{$input.name|escape:'html':'UTF-8'}').val());
            if (!!dates[date]) {
              var item = dates[date];
              if (item.cutoff) {
                {$input.name|escape:'javascript':'UTF-8'}setOtherDispatch(item.cutoff);
              } else {
                {$input.name|escape:'javascript':'UTF-8'}setNoDispatch();
              }
            } else {
              {$input.name|escape:'javascript':'UTF-8'}setDispatch();
            }
          }

          $('#{$input.name|escape:'html':'UTF-8'}_datetitle').text(moment(date, 'DD-MM-YYYY').format('DD MMMM YYYY'));
        }

        function {$input.name|escape:'javascript':'UTF-8'}setDispatch() {
          $('#{$input.name|escape:'javascript':'UTF-8'}-nodispatch-btn').addClass('btn-default').removeClass('btn-danger');
          $('#{$input.name|escape:'javascript':'UTF-8'}-otherdispatch-btn').addClass('btn-default').removeClass('btn-warning');
          $('#{$input.name|escape:'javascript':'UTF-8'}-dispatch-btn').addClass('btn-success').removeClass('btn-default');
          $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val('');
        }

        function {$input.name|escape:'javascript':'UTF-8'}setOtherDispatch(cutoff) {
          $('#{$input.name|escape:'javascript':'UTF-8'}-nodispatch-btn').addClass('btn-default').removeClass('btn-danger');
          $('#{$input.name|escape:'javascript':'UTF-8'}-otherdispatch-btn').addClass('btn-warning').removeClass('btn-default');
          $('#{$input.name|escape:'javascript':'UTF-8'}-dispatch-btn').addClass('btn-default').removeClass('btn-success');
          $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val(cutoff);
        }

        function {$input.name|escape:'javascript':'UTF-8'}setNoDispatch() {

          $('#{$input.name|escape:'javascript':'UTF-8'}-nodispatch-btn').addClass('btn-danger').removeClass('btn-default');
          $('#{$input.name|escape:'javascript':'UTF-8'}-otherdispatch-btn').addClass('btn-default').removeClass('btn-warning');
          $('#{$input.name|escape:'javascript':'UTF-8'}-dispatch-btn').addClass('btn-default').removeClass('btn-success');
          $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val('');
        }

        function {$input.name|escape:'javascript':'UTF-8'}addDate(date) {
          var dates = JSON.parse($('#{$input.name|escape:'html':'UTF-8'}').val());
          dates[date] = {
            "nodispatch": true
          };
          $('#{$input.name|escape:'html':'UTF-8'}').val(JSON.stringify(dates));
        }

        function {$input.name|escape:'javascript':'UTF-8'}addCutOff(date, cutoff) {
          var dates = JSON.parse($('#{$input.name|escape:'html':'UTF-8'}').val());
          dates[date] = {
            "nodispatch": true,
            "cutoff": cutoff
          };
          $('#{$input.name|escape:'html':'UTF-8'}').val(JSON.stringify(dates));
        }

        function {$input.name|escape:'javascript':'UTF-8'}removeDate(date) {
          var dates = JSON.parse($('#{$input.name|escape:'html':'UTF-8'}').val());
          delete dates[date];
          $('#{$input.name|escape:'html':'UTF-8'}').val(JSON.stringify(dates));
        }

        $(document).ready(function () {
          $('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').datepicker({
            dateFormat: 'dd-mm-yy',
            beforeShowDay: {$input.name|escape:'javascript':'UTF-8'}highlightDays,
            minDate: 0,
            onSelect: {$input.name|escape:'javascript':'UTF-8'}dateSelect
          });
          $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').timepicker({
            timeOnly: true,
            timeFormat: 'hh:mm'
          });
          $('#{$input.name|escape:'javascript':'UTF-8'}-dispatch-btn').click(function () {
            {$input.name|escape:'javascript':'UTF-8'}removeDate($('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val());
            {$input.name|escape:'javascript':'UTF-8'}setDispatch();
          });
          $('#{$input.name|escape:'javascript':'UTF-8'}-otherdispatch-btn').click(function () {
            if ($('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val()) {
              {$input.name|escape:'javascript':'UTF-8'}removeDate($('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val());
              {$input.name|escape:'javascript':'UTF-8'}addCutOff(
                $('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val(),
                $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val()
              );
            }
            {$input.name|escape:'javascript':'UTF-8'}setOtherDispatch($('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val());
          });
          $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').change(function () {
            if ($(this).val()) {
              {$input.name|escape:'javascript':'UTF-8'}removeDate($('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val());
              {$input.name|escape:'javascript':'UTF-8'}addCutOff(
                $('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val(),
                $('#{$input.name|escape:'javascript':'UTF-8'}-cutoff').val()
              );
              {$input.name|escape:'javascript':'UTF-8'}setOtherDispatch($(this).val());
            }
          });
          $('#{$input.name|escape:'javascript':'UTF-8'}-nodispatch-btn').click(function () {
            {$input.name|escape:'javascript':'UTF-8'}removeDate($('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val());
            {$input.name|escape:'javascript':'UTF-8'}addDate($('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').val());
            {$input.name|escape:'javascript':'UTF-8'}setNoDispatch();
          });
          var current_date = new Date($('#datepicker_{$input.name|escape:'javascript':'UTF-8'}').datepicker('getDate')),
            yr = current_date.getFullYear(),
            month = (current_date.getMonth() + 1) < 10 ? '0' + (current_date.getMonth() + 1) : (current_date.getMonth() + 1),
            day = current_date.getDate() < 10 ? '0' + current_date.getDate() : current_date.getDate(),
            new_current_date = day + '-' + month + '-' + yr;
          {$input.name|escape:'javascript':'UTF-8'}dateSelect(new_current_date);
        });
      }());
    </script>
  {else}
    {$smarty.block.parent}
  {/if}
{/block}

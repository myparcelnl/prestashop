{*
 * 2007-2016 PrestaShop
 * 2018 DM Productions B.V.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @author    Michael Dekker <info@mijnpresta.nl
 *  @copyright 2007-2016 PrestaShop SA
 *  @copyright 2018 D.M. Productions B.V.
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}
{capture name="onboardingStepParagraph"}
  {if $shouldResume}
    {l s='If you are done making changes, please click "Resume" to continue where you left off.' sprintf=[$employee->firstname] tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 0}
    {l s='Hi %s, welcome to the new MyParcel module.[1]In order to quickly get you onboard with this module we provide a small tour.' sprintf=[$employee->firstname] tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 1}
    {l s='We will now walk you through all the necessary MyParcel configuration options.[1]You can enter the required information during the tour. We will give you some useful tips as well! In just a few minutes you will be ready to go send your first shipments with this module.[1]Please start by entering your API key.' tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 2}
    {l s='On this page you can set the preferred delivery options. These will be displayed on the checkout page, which is where the customer can pick the preferred delivery method.[1]The settings will be tied to a carrier, so it is possible to add multiple carriers with different settings.[1]Most of the domestic shipment options are available for shipments to Belgium as well.' tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 3}
    {l s='The checkout can be fully adjusted to your likings on this page. The panel below allows you to change the colors and font. The changes are shown in realtime, so you immediately get to see the end result in the preview box.[1]As soon as you`re ready, you can save the settings. Not quite what you had in mind? Click the reset button to revert to factory settings.' mod='myparcel' tags=['<br>']}
  {elseif $current_step == 4}
    {l s='At the bottom of this page you will find the possibility to change default label descriptions. It is possible to further personalize a label by uploading your logo. This can be done with the MyParcel back ofice.[1]Beneath this setting you can adjust the paper format and persist your choice by unticking the option "Always prompt for the paper size."[1]Order statuses can be automated on this page as well. Choose one or more of the available automation statuses and they will be applied instantly. If you have several statuses that may never change, pick them from the list just underneath. The module will skip updating the order as soon as they have one of the chosen blacklisted order statuses.' mod='myparcel' tags=['<br>'] sprintf=['PrestaShop']}
  {elseif $current_step == 5}
    {l s='The wizard on this page allows you to configure the carrier. We have already configured a few items.[1]The next steps are where you can adjust the fees, assign customer groups and eventually enable the carrier so it becomes available your customers.' tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 6}
    {l s='From now on the order list and page will display MyParcel icons. By clicking an icon you get the possibility to quickly generate and print a label.[1]For more information you can always refer to the [2]full manual[/2].' tags=['<br>', '<a href="https://myparcelnl.github.io/prestashop" class="_blank" rel="noopener noreferrer">'] mod='myparcel'}
  {/if}
{/capture}
{capture name="onboardingStepButton"}
  {if $shouldResume}
    {l s='Resume' sprintf=[$employee->firstname] tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 0}
    {l s='Let`s start!' mod='myparcel'}
  {elseif $current_step == 6}
    {l s='Let`s begin sending!' mod='myparcel'}
  {else}
    {l s='Next step' mod='myparcel'}
  {/if}
{/capture}
{capture name="onboardingStepBannerTitle"}
  {if $shouldResume}
    {l s='Hi %s' sprintf=[$employee->firstname] tags=['<br>'] mod='myparcel'}
  {elseif $current_step == 0}
    {l s='Take a tour: get started with MyParcel' mod='myparcel'}
  {elseif $current_step == 1}
    {l s='Enter your API key' mod='myparcel'}
  {elseif $current_step == 2}
    {l s='Configure your delivery options' mod='myparcel'}
  {elseif $current_step == 3}
    {l s='Design your checkout' mod='myparcel'}
  {elseif $current_step == 4}
    {l s='Labels and notifications' mod='myparcel'}
  {elseif $current_step == 5}
    {l s='Configure the carrier' mod='myparcel'}
  {elseif $current_step == 6}
    {l s='Start shipping with MyParcel' mod='myparcel'}
  {/if}
{/capture}

<div id="myparcel-onboarding">
  <div class="alert alert-onboarding">
    <img id="myparcel-onboarding-logo" src="{$module_dir|escape:'htmlall'}views/img/myparcelnl.png" width="250" height="250">
    <div id="myparcel-onboarding-starter">
      <div class="row">
        <div class="col-md-12">
          <h3>{l s='Getting Started with MyParcel' mod='myparcel'}</h3>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-2 col-md-2">
          <div class="onboarding-step step-first {if $current_step < 1}step-todo{elseif $current_step == 1}step-in-progress active{elseif $current_step > 1}active step-success{/if}"></div>
        </div>
        <div class="col-xs-2 col-md-2">
          <div class="onboarding-step {if $current_step <= 1}step-todo{elseif $current_step == 2}step-in-progress active{elseif $current_step > 2}active step-success{/if}"></div>
        </div>
        <div class="col-xs-2 col-md-2">
          <div class="onboarding-step {if $current_step <= 2}step-todo{elseif $current_step == 3}step-in-progress active{elseif $current_step > 3}active step-success{/if}"></div>
        </div>
        <div class="col-xs-2 col-md-2">
          <div class="onboarding-step {if $current_step <= 3}step-todo{elseif $current_step == 4}step-in-progress active{elseif $current_step > 4}active step-success{/if}"></div>
        </div>
        <div class="col-xs-2 col-md-2">
          <div class="onboarding-step {if $current_step <= 4}step-todo{elseif $current_step == 5}step-in-progress active{elseif $current_step > 5}active step-success{/if}"></div>
        </div>
        <div class="col-xs-2 col-md-2">
          <div class="onboarding-step step-final {if $current_step <= 5}step-todo{elseif $current_step == 6}step-in-progress active{elseif $current_step > 6}active step-success{/if}"></div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-2 col-md-2 text-center">
          <a style="{if $current_step < 1} color:gray; text-decoration:none {/if}"
             {if $current_step > 1}href="{$moduleUrl|escape:'htmlall'}&tour_step=1&tour_redirect=1"{/if}
          >
            {l s='Enter your API key' mod='myparcel'}
          </a>
        </div>
        <div class="col-xs-2 col-md-2 text-center">
          <a style="{if $current_step < 2} color:gray; text-decoration:none {/if}"
             {if $current_step > 2}href="{$moduleUrl|escape:'htmlall'}&tour_step=2&tour_redirect=1"{/if}
          >
            {l s='Configure your checkout' mod='myparcel'}
          </a>
        </div>
        <div class="col-xs-2 col-md-2 text-center">
          <a style="{if $current_step < 3} color:gray; text-decoration:none {/if}"
             {if $current_step > 3}href="{$moduleUrl|escape:'htmlall'}&tour_step=3&tour_redirect=1"{/if}

          >
            {l s='Design your checkout' mod='myparcel'}
          </a>
        </div>
        <div class="col-xs-2 col-md-2 text-center">
          <a style="{if $current_step < 4} color:gray; text-decoration:none {/if}"
             {if $current_step > 4}href="{$moduleUrl|escape:'htmlall'}&tour_step=4&tour_redirect=1"{/if}
          >
            {l s='Labels and notifications' mod='myparcel'}
          </a>
        </div>
        <div class="col-xs-2 col-md-2 text-center">
          <a style="{if $current_step < 5} color:gray; text-decoration:none {/if}"
             {if $current_step > 5}href="{$moduleUrl|escape:'htmlall'}&tour_step=5&tour_redirect=1"{/if}
          >
            {l s='Configure the carrier' mod='myparcel'}
          </a>
        </div>
        <div class="col-xs-2 col-md-2 text-center">
          <a style="{if $current_step < 6} color:gray; text-decoration:none {/if}"
             {if $current_step > 6}href="{$moduleUrl|escape:'htmlall'}&tour_step=6&tour_redirect=1"{/if}
          >
            {l s='Start shipping' mod='myparcel'}
          </a>
        </div>
      </div>
      <hr>
      <div class="row">
        <div class="col-lg-8">
          <h4>{$smarty.capture.onboardingStepBannerTitle}</h4>
          <p>{$smarty.capture.onboardingStepParagraph}</p>
        </div>
        <div class="col-lg-4 onboarding-action-container">
          <a id="myparcel-onboarding-next"
             href="{$moduleUrl|escape:'htmlall'}&tour_step={if $shouldResume && $smarty.get.controller !== 'AdminCarriers'}{$current_step}{else}{$current_step + 1}{/if}&tour_redirect=1"
             class="btn btn-default btn-lg quick-start-button pull-right"
          >
            {$smarty.capture.onboardingStepButton}&nbsp;&nbsp;
            <i class="icon icon-chevron-right icon-lg"></i>
          </a>
          <a id="myparcel-onboarding-close"
             class="btn btn-default btn-lg pull-right"
             href="{$moduleUrl|escape:'htmlall'}&tour_step=99"
          >
            {l s='No thanks!' mod='myparcel'}&nbsp;&nbsp;
            <i class="icon icon-times icon-lg"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  {* Install polyfills *}
  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.msMatchesSelector ||
      Element.prototype.webkitMatchesSelector;
  }

  if (!Element.prototype.closest) {
    Element.prototype.closest = function (s) {
      var el = this;
      if (!document.documentElement.contains(el)) return null;
      do {
        if (el.matches(s)) return el;
        el = el.parentElement || el.parentNode;
      } while (el !== null && el.nodeType === 1);
      return null;
    };
  }

  (function initMyParcelOnboarding() {
    var content = document.getElementById('content');
    if (content == null) {
      return setTimeout(initMyParcelOnboarding, 100);
    }
    var onboardingDiv = document.getElementById('myparcel-onboarding');
    var outlineElements = [onboardingDiv];

    function ajaxEndTour(callback) {
      var request = new XMLHttpRequest();
      request.open('GET', '{$moduleUrl|escape:'javascript'}&ajax=1&action=SetTourStep&tour_step=99', true);

      request.onreadystatechange = function() {
        if (this.readyState === 4) {
          if (this.status >= 200 && this.status < 400) {
            onboardingDiv.style.display = 'none';

            {* Restore tour elements if necessary *}
            {if $tourElement}
            outlineElements.forEach(function (item) {
              item.style.zIndex = 0;
              item.style.outline = 'none';
            });
            {/if}

            if (typeof callback === 'function') {
              return callback();
            }
          }
        }
      };

      request.send();
      request = null;
    }

    function submitForm(callback) {
      {if $submitForm}
        var form = document.querySelector('{$submitForm|escape:'javascript'}');
        if (form) {
          var request = new XMLHttpRequest();
          request.open('POST', form.action, true);

          request.onreadystatechange = function() {
            if (this.readyState === 4) {
              callback(this.status >= 200 && this.status < 400);
            }
          };

          var formData = new FormData(form);
          {foreach $extraSubmits as $extraSubmit => $value}
            formData.append('{$extraSubmit|escape:'javascript'}', '{$value|escape:'javascript'}');
          {/foreach}
          request.send(formData);
          request = null;
        } else {
          callback(true);
        }
      {else}
        callback(true);
      {/if}
    }

    var d = document.createElement('div');
    d.style.outline = 'rgba(0, 0, 0, 0.5) solid 9999px';
    if (d.style.outline !== 'rgba(0, 0, 0, 0.5) solid 9999px') {
      onboardingDiv.parentElement.removeChild(onboardingDiv);
      console.log('Tour disabled, requires a modern browser');
      ajaxEndTour();
      return;
    }

    if (onboardingDiv && content) {
      content.insertBefore(onboardingDiv, content.firstChild);

      {* Focus on the tour element -- only when the tour has started *}
      {if $tourElement && !$shouldResume}
        onboardingDiv.style.position = 'relative';
        onboardingDiv.style.zIndex = '90000';
        onboardingDiv.style.outline = 'rgba(0, 0, 0, 0.5) solid 9999px';
        [].slice.call(document.querySelectorAll('{$tourElement|escape:'javascript'}')).forEach(function (onboardingElement) {
          outlineElements.push(onboardingElement);
          onboardingElement.style.zIndex = '90001';
        });
      {/if}
    }

    var onboardingNext = document.getElementById('myparcel-onboarding-next');
    if (onboardingNext) {
      {if $current_step != 6}
        onboardingNext.onclick = function (event) {
          event.preventDefault();
          var target = event.target.closest('A');
          submitForm(function (success) {
            if (success) {
              window.location = target.href;
            } else {
              showErrorMessage('{l s='Unable to continue. Could not save the current settings.' mod='myparcel' js=1}');
            }
          });
        };
      {else}
        onboardingNext.onclick = function (event) {
          event.preventDefault();
          ajaxEndTour();
        };
      {/if}
    }

    var onboardingClose = document.getElementById('myparcel-onboarding-close');
    if (onboardingClose) {
      onboardingClose.onclick = function (event) {
        event.preventDefault();
        ajaxEndTour();
      };
    }

  }());
</script>

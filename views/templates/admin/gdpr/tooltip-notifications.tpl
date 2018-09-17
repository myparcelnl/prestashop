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
<p>
  {l s='Sending notifications through MyParcel is not required for a successful delivery.' mod='myparcel'}
  {l s='If you enable this option in the MyParcel back office and start sharing the email addresses with MyParcel, your store might no longer be GDPR compliant.' mod='myparcel'}
</p>
<p>
  {l s='Therefore, if you do not have this kind of data sharing specified in your terms & conditions and/or privacy policy, or if you do not wish to do so, it is generally better to enable this option so the module sends emails directly through' mod='myparcel'}
  {if $smarty.const._TB_VERSION_}
    thirty bees.
  {else}
    PrestaShop.
  {/if}
</p>

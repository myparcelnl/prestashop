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
<p>
  {if $gdprType === 'email'}
    {assign var='gdprEntity' value={l s='email address' mod='myparcel'}}
  {else}
    {assign var='gdprEntity' value={l s='phone number' mod='myparcel'}}
  {/if}
  {l s='Sharing your customer`s %s is not required for a successful delivery.' mod='myparcel' sprintf=[$gdprEntity]}
  {l s='If you enable this option, your store might no longer be GDPR compliant.' mod='myparcel'}
</p>
<p>
  {l s='Therefore, if you do not have this kind of data sharing specified in your terms & conditions and/or privacy policy, or if you do not wish to do so, it is generally better to turn off this option.' mod='myparcel'}
</p>

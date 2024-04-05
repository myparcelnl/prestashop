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
{if isset($this_version) && isset($release_version)}
  <div class="alert alert-warning">
    <span id="myparcel_update_msg">
  {l s='You are currently using version %s. We strongly recommend you to upgrade to the new version %s!' mod='myparcel' sprintf=[$this_version, $release_version]}
    </span>
    {include file="./download_update.tpl"}
  </div>
{/if}

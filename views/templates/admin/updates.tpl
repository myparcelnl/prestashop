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
<div class="panel">
  <h3><i class="icon icon-refresh"></i> {l s='Check for updates' mod='myparcel'}</h3>
  <p>
    <strong>{l s='Check if this module needs updates' mod='myparcel'}</strong><br/>
  </p>
  <div class="alert alert-info">
    {l s='When you check for updates, the module will automatically install the update after an update has been found' mod='myparcel'}
  </div>
  <a class="btn btn-default" href="{$module_url|escape:'htmlall':'UTF-8' nofilter}&myparcelCheckForUpdates=1"><i class="icon icon-search"></i> {l s='Check for updates' mod='myparcel'}</a>
</div>

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
<nav class="navbar navbar-default" role="navigation">
  <ul class="nav navbar-nav">
    {if isset($menutabs)}
      {foreach from=$menutabs item=tab}
        <li class="{if $tab.active}active{/if}">
          <a id="{$tab.short|escape:'htmlall':'UTF-8' nofilter}" href="{$tab.href|escape:'htmlall':'UTF-8' nofilter}">
            <span class="icon {$tab.icon|escape:'htmlall':'UTF-8' nofilter}"></span>
            {$tab.short|escape:'htmlall':'UTF-8' nofilter}
          </a>
        </li>
      {/foreach}
    {/if}
  </ul>
</nav>

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
{l s='Please enter a custom label description.' mod='myparcel'}<br/>
{l s='You can add the following variables to the description:' mod='myparcel'}<br/>
<ul>
  <li>
    <kbd class="label-code" onclick="addLabelVar('{ldelim}order.id{rdelim}');" style="cursor: pointer">
      {ldelim}order.id{rdelim}
    </kbd>
    &nbsp;- {l s='Order ID' mod='myparcel'}
  </li>
  <li>
    <kbd class="label-code" onclick="addLabelVar('{ldelim}order.reference{rdelim}');" style="cursor: pointer">
      {ldelim}order.reference{rdelim}
    </kbd>
    &nbsp;- {l s='Order reference' mod='myparcel'}
  </li>
</ul>


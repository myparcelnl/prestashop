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
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_monday value=$tr['cutoff_times'][0]}
{/if}
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_tuesday value=$tr['cutoff_times'][1]}
{/if}
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_wednesday value=$tr['cutoff_times'][2]}
{/if}
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_thursday value=$tr['cutoff_times'][3]}
{/if}
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_friday value=$tr['cutoff_times'][4]}
{/if}
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_saturday value=$tr['cutoff_times'][5]}
{/if}
{if isset($tr['cutoff_times'][0])}
  {assign var=cutoff_sunday value=$tr['cutoff_times'][6]}
{/if}
{if isset($cutoff_monday)}
  {if !$cutoff_monday['nodispatch'] && !$cutoff_monday['exception']}
    <span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_monday['name']|escape:'htmlall'}</span> {$cutoff_monday['time']|escape:'htmlall'}</span>
  {elseif $cutoff_monday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_monday['name']|escape:'htmlall'}</span></span>
  {else}
    <span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_monday['name']|escape:'htmlall'}</span> {$cutoff_monday['time']|escape:'htmlall'}</span>
  {/if}
{/if}
{if isset($cutoff_tuesday)}
  {if !$cutoff_tuesday['nodispatch'] && !$cutoff_tuesday['exception']}
    <span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_tuesday['name']|escape:'htmlall'}</span> {$cutoff_tuesday['time']|escape:'htmlall'}</span>
  {elseif $cutoff_tuesday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_tuesday['name']|escape:'htmlall'}</span></span>
  {else}
    <span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_tuesday['name']|escape:'htmlall'}</span> {$cutoff_tuesday['time']|escape:'htmlall'}</span>
  {/if}
{/if}
{if isset($cutoff_wednesday)}
  {if !$cutoff_wednesday['nodispatch'] && !$cutoff_wednesday['exception']}
    <span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_wednesday['name']|escape:'htmlall'}</span> {$cutoff_wednesday['time']|escape:'htmlall'}</span>
  {elseif $cutoff_wednesday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_wednesday['name']|escape:'htmlall'}</span></span>
  {else}
    <span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_wednesday['name']|escape:'htmlall'}</span> {$cutoff_wednesday['time']|escape:'htmlall'}</span>
  {/if}
{/if}
<br class="visible-sm visible-xs visible-md">
{if isset($cutoff_thursday)}
  {if !$cutoff_thursday['nodispatch'] && !$cutoff_thursday['exception']}
    <span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_thursday['name']|escape:'htmlall'}</span> {$cutoff_thursday['time']|escape:'htmlall'}</span>
  {elseif $cutoff_thursday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_thursday['name']|escape:'htmlall'}</span></span>
  {else}
    <span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_thursday['name']|escape:'htmlall'}</span> {$cutoff_thursday['time']|escape:'htmlall'}</span>
  {/if}
{/if}
{if isset($cutoff_friday)}
  {if !$cutoff_friday['nodispatch'] && !$cutoff_friday['exception']}
    <span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_friday['name']|escape:'htmlall'}</span> {$cutoff_friday['time']|escape:'htmlall'}</span>
  {elseif $cutoff_friday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_friday['name']|escape:'htmlall'}</span></span>
  {else}
    <span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_friday['name']|escape:'htmlall'}</span> {$cutoff_friday['time']|escape:'htmlall'}</span>
  {/if}
{/if}
{if isset($cutoff_saturday)}
  {if !$cutoff_saturday['nodispatch'] && !$cutoff_saturday['exception']}
    <span class="label myparcel-label myparcel-label-success"><span class="badge myparcel-badge-success">{$cutoff_saturday['name']|escape:'htmlall'}</span> {$cutoff_saturday['time']|escape:'htmlall'}</span>
  {elseif $cutoff_saturday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger"><span class="badge myparcel-badge-danger">{$cutoff_saturday['name']|escape:'htmlall'}</span></span>
  {else}
    <span class="label myparcel-label myparcel-label-warning"><span class="badge myparcel-badge-warning">{$cutoff_saturday['name']|escape:'htmlall'}</span> {$cutoff_saturday['time']|escape:'htmlall'}</span>
  {/if}
{/if}
<br class="visible-sm visible-xs visible-md">
{if isset($cutoff_sunday)}
  {if !$cutoff_sunday['nodispatch'] && !$cutoff_sunday['exception']}
    <span class="label myparcel-label myparcel-label-success">
          <span class="badge myparcel-badge-success">{$cutoff_sunday['name']|escape:'htmlall'}</span> {$cutoff_sunday['time']|escape:'htmlall'}
        </span>
  {elseif $cutoff_sunday['nodispatch']}
    <span class="label myparcel-label myparcel-label-danger">
        <span class="badge myparcel-badge-danger">{$cutoff_sunday['name']|escape:'htmlall'}</span>
      </span>
  {else}
    <span class="label myparcel-label myparcel-label-warning">
        <span class="badge myparcel-badge-warning">{$cutoff_sunday['name']|escape:'htmlall'}</span> {$cutoff_sunday['time']|escape:'htmlall'}
      </span>
  {/if}
{/if}

{if isset($prestaShopVersion) && $prestaShopVersion == "1.5"}
	<tbody>
	{if count($list)}
	{foreach $list AS $index => $tr}
		<tr
		{if $position_identifier}id="tr_{$id_category}_{$tr.$identifier}_{if isset($tr.position['position'])}{$tr.position['position']}{else}0{/if}"{/if}
		class="{if $index is odd}alt_row{/if} {if $row_hover}row_hover{/if}"
		{if isset($tr.color) && $color_on_bg}style="background-color: {$tr.color}"{/if}
		>
			<td class="center">
				{if {$has_bulk_actions}}
					{if isset($list_skip_actions.delete)}
						{if !in_array($tr.$identifier, $list_skip_actions.delete)}
							<input type="checkbox" name="{$table}Box[]" value="{$tr.$identifier}" class="noborder" />
						{/if}
					{else}
						<input type="checkbox" name="{$table}Box[]" value="{$tr.$identifier}" class="noborder" />
					{/if}
				{/if}
			</td>
			{foreach $fields_display AS $key => $params}
				{block name="open_td"}
					<td
						{if isset($params.position)}
							id="td_{if !empty($id_category)}{$id_category}{else}0{/if}_{$tr.$identifier}"
						{/if}
						class="{if !$no_link}pointer{/if}
						{if isset($params.position) && $order_by == 'position'  && $order_way != 'DESC'} dragHandle{/if}
						{if isset($params.align)} {$params.align}{/if}"
						{if (!isset($params.position) && !$no_link && !isset($params.remove_onclick))}
							onclick="document.location = '{$current_index}&{$identifier}={$tr.$identifier}{if $view}&view{else}&update{/if}{$table}&token={$token}'">
						{else}
						>
					{/if}
				{/block}
				{block name="td_content"}
					{if isset($params.prefix)}{$params.prefix}{/if}
					{if isset($params.color) && isset($tr[$params.color])}
						<span class="color_field" style="background-color:{$tr.color};color:{if Tools::getBrightness($tr.color) < 128}white{else}#383838{/if}">
					{/if}
					{if isset($tr.$key)}
						{if isset($params.active)}
							{$tr.$key}
						{elseif isset($params.activeVisu)}
							<img src="../img/admin/{if $tr.$key}enabled.gif{else}disabled.gif{/if}"
							alt="{if $tr.$key}{l s='Enabled'}{else}{l s='Disabled'}{/if}" title="{if $tr.$key}{l s='Enabled'}{else}{l s='Disabled'}{/if}" />
						{elseif isset($params.position)}
							{if $order_by == 'position' && $order_way != 'DESC'}
								<a href="{$tr.$key.position_url_down}" {if !($tr.$key.position != $positions[count($positions) - 1])}style="display: none;"{/if}>
									<img src="../img/admin/{if $order_way == 'ASC'}down{else}up{/if}.gif" alt="{l s='Down'}" title="{l s='Down'}" />
								</a>
	
								<a href="{$tr.$key.position_url_up}" {if !($tr.$key.position != $positions.0)}style="display: none;"{/if}>
									<img src="../img/admin/{if $order_way == 'ASC'}up{else}down{/if}.gif" alt="{l s='Up'}" title="{l s='Up'}" />
								</a>
							{else}
								{$tr.$key.position + 1}
							{/if}
						{elseif isset($params.image)}
							{$tr.$key}
						{elseif isset($params.icon)}
							{if is_array($tr[$key])}
								<img src="../img/admin/{$tr[$key]['src']}" alt="{$tr[$key]['alt']}" title="{$tr[$key]['alt']}" />
							{/if}
						{elseif isset($params.price)}
							{$tr.$key}
						{elseif isset($params.float)}
							{$tr.$key}
						{elseif isset($params.type) && $params.type == 'date'}
							{$tr.$key}
						{elseif isset($params.type) && $params.type == 'datetime'}
							{$tr.$key}
						{elseif isset($params.type) && $params.type == 'decimal'}
							{$tr.$key|string_format:"%.2f"}
						{elseif isset($params.type) && $params.type == 'percent'}
							{$tr.$key} {l s='%'}
						{* If type is 'editable', an input is created *}
						{elseif isset($params.type) && $params.type == 'editable' && isset($tr.id)}
							<input type="text" name="{$key}_{$tr.id}" value="{$tr.$key|escape:'htmlall':'UTF-8'}" class="{$key}" />
						{elseif isset($params.callback)}
							{$tr.$key}
						{elseif $key == 'color'}
							<div style="float: left; width: 18px; height: 12px; border: 1px solid #996633; background-color: {$tr.$key}; margin-right: 4px;"></div>
						{elseif isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
							<span title="{$tr.$key|escape:'htmlall':'UTF-8'}">{$tr.$key|truncate:$params.maxlength:'...'|escape:'htmlall':'UTF-8'}</span>
						{else}
							{$tr.$key|escape:'htmlall':'UTF-8'}
						{/if}
					{else}
						{block name="default_field"}--{/block}
					{/if}
					{if isset($params.suffix)}{$params.suffix}{/if}
					{if isset($params.color) && isset($tr.color)}
						</span>
					{/if}
				{/block}
				{block name="close_td"}
					</td>
				{/block}
			{/foreach}
	
		{if isset($myParcel) && $myParcel == 'true'}
			{assign var = myParcelOrderData value = MyParcel::getOrderData($tr.$identifier)}
	
			<td class="myparcel_align_right">
				<table class="myparcel_table">
					<tr>
						<td id="mypa_exist_{$tr.$identifier}">
							<div>
	       						<input type="checkbox" value="{$myParcelOrderData.checks}" id="mypa_check_{$tr.$identifier}" class="mypaleft mypacheck">
	       						{$myParcelOrderData.items}
	       					</div>
						</td>
						<td class="mypafunc">
							<a href="#" class="myparcel-consignment-new" onclick="return createNewConsignment('{$tr.$identifier}');"><img src="/modules/myparcel/images/myparcel_pdf_add.png" alt="New"></a>
							<a href="#" class="myparcel-consignment-retour" onclick="return createNewConsignment('{$tr.$identifier}', true);"><img src="/modules/myparcel/images/myparcel_retour_add.png" alt="Retour"></a>
						</td>
					</tr>
				</table>
			</td>
		{/if}
	
	    {if $shop_link_type}
	        <td class="center" title="{$tr.shop_name}">
	            {if isset($tr.shop_short_name)}
	                {$tr.shop_short_name}
	            {else}
	                {$tr.shop_name}
	            {/if}</td>
	    {/if}
	
		{if $has_actions}
			<td class="center" style="white-space: nowrap;">
				{foreach $actions AS $action}
					{if isset($tr.$action)}
						{$tr.$action}
					{/if}
				{/foreach}
			</td>
		{/if}
		</tr>
	{/foreach}
	{else}
		<tr><td class="center" colspan="{count($fields_display) + 2}">{l s='No items found'}</td></tr>
	{/if}
	</tbody>
{else}
	<tbody>
	{if count($list)}
	{foreach $list AS $index => $tr}
		<tr
		{if $position_identifier}id="tr_{$position_group_identifier}_{$tr.$identifier}_{if isset($tr.position['position'])}{$tr.position['position']}{else}0{/if}"{/if}
		class="{if isset($tr.class)} {$tr.class}{/if} {if $tr@iteration is odd by 1}odd{/if}"
		{if isset($tr.color) && $color_on_bg}style="background-color: {$tr.color}"{/if}
	
		>
			<td class="text-center">
				{if $bulk_actions && $has_bulk_actions}
					{if isset($list_skip_actions.delete)}
						{if !in_array($tr.$identifier, $list_skip_actions.delete)}
							<input type="checkbox" name="{$list_id}Box[]" value="{$tr.$identifier}"{if isset($checked_boxes) && is_array($checked_boxes) && in_array({$tr.$identifier}, $checked_boxes)} checked="checked"{/if} class="noborder" />
						{/if}
					{else}
						<input type="checkbox" name="{$list_id}Box[]" value="{$tr.$identifier}"{if isset($checked_boxes) && is_array($checked_boxes) && in_array({$tr.$identifier}, $checked_boxes)} checked="checked"{/if} class="noborder" />
					{/if}
				{/if}
			</td>
			{foreach $fields_display AS $key => $params}
				{block name="open_td"}
					<td
						{if isset($params.position)}
							id="td_{if !empty($position_group_identifier)}{$position_group_identifier}{else}0{/if}_{$tr.$identifier}"
						{/if}
						class="{if !$no_link}pointer{/if}
						{if isset($params.position) && $order_by == 'position'  && $order_way != 'DESC'} dragHandle{/if}
						{if isset($params.class)} {$params.class}{/if}
						{if isset($params.align)} {$params.align}{/if}"
						{if (!isset($params.position) && !$no_link && !isset($params.remove_onclick))}
							onclick="document.location = '{$current_index}&{$identifier}={$tr.$identifier}{if $view}&view{else}&update{/if}{$table}&token={$token}'">
						{else}
						>
					{/if}
				{/block}
				{block name="td_content"}
					{if isset($params.prefix)}{$params.prefix}{/if}
					{if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}<span class="badge badge-success">{/if}
					{if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}<span class="badge badge-warning">{/if}
					{if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}<span class="badge badge-danger">{/if}
					{if isset($params.color) && isset($tr[$params.color])}
						<span class="label color_field" style="background-color:{$tr[$params.color]};color:{if Tools::getBrightness($tr[$params.color]) < 128}white{else}#383838{/if}">
					{/if}
					{if isset($tr.$key)}
						{if isset($params.active)}
							{$tr.$key}
						{elseif isset($params.activeVisu)}
							{if $tr.$key}
								<i class="icon-check-ok"></i> {l s='Enabled'}
							{else}
								<i class="icon-remove"></i> {l s='Disabled'}
							{/if}
	
						{elseif isset($params.position)}
							{if $order_by == 'position' && $order_way != 'DESC'}
								<div class="dragGroup">
									<div class="positions">
										{$tr.$key.position}
									</div>
									<div class="btn-group">
										<a class="btn btn-default btn-xs" href="{$tr.$key.position_url_down}" {if !($tr.$key.position != $positions[count($positions) - 1])}disabled{/if}>
											<i class="icon-chevron-{if $order_way == 'ASC'}down{else}up{/if}"></i>
										</a>
										<a class="btn btn-default btn-xs" href="{$tr.$key.position_url_up}" {if !($tr.$key.position != $positions.0)}disabled{/if}>
											<i class="icon-chevron-{if $order_way == 'ASC'}up{else}down{/if}"></i>
										</a>
									</div>
								</div>
							{else}
								{$tr.$key.position + 1}
							{/if}
						{elseif isset($params.image)}
							{$tr.$key}
						{elseif isset($params.icon)}
							{if is_array($tr[$key])}
								{if isset($tr[$key]['class'])}
									<i class="{$tr[$key]['class']}"></i>
								{else}
									<img src="../img/admin/{$tr[$key]['src']}" alt="{$tr[$key]['alt']}" title="{$tr[$key]['alt']}" />
								{/if}
	                        {else}
	                            <i class="{$tr[$key]}"></i>
							{/if}
						{elseif isset($params.type) && $params.type == 'price'}
							{displayPrice price=$tr.$key}
						{elseif isset($params.float)}
							{$tr.$key}
						{elseif isset($params.type) && $params.type == 'date'}
							{dateFormat date=$tr.$key full=0}
						{elseif isset($params.type) && $params.type == 'datetime'}
							{dateFormat date=$tr.$key full=1}
						{elseif isset($params.type) && $params.type == 'decimal'}
							{$tr.$key|string_format:"%.2f"}
						{elseif isset($params.type) && $params.type == 'percent'}
							{$tr.$key} {l s='%'}
						{* If type is 'editable', an input is created *}
						{elseif isset($params.type) && $params.type == 'editable' && isset($tr.id)}
							<input type="text" name="{$key}_{$tr.id}" value="{$tr.$key|escape:'html':'UTF-8'}" class="{$key}" />
						{elseif isset($params.callback)}
							{if isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
								<span title="{$tr.$key}">{$tr.$key|truncate:$params.maxlength:'...'}</span>
							{else}
								{$tr.$key}
							{/if}
						{elseif $key == 'color'}
							{if !is_array($tr.$key)}
							<div style="background-color: {$tr.$key};" class="attributes-color-container"></div>
							{else} {*TEXTURE*}
							<img src="{$tr.$key.texture}" alt="{$tr.name}" class="attributes-color-container" />
							{/if}
						{elseif isset($params.maxlength) && Tools::strlen($tr.$key) > $params.maxlength}
							<span title="{$tr.$key|escape:'html':'UTF-8'}">{$tr.$key|truncate:$params.maxlength:'...'|escape:'html':'UTF-8'}</span>
						{else}
							{$tr.$key|escape:'html':'UTF-8'}
						{/if}
					{else}
						{block name="default_field"}--{/block}
					{/if}
					{if isset($params.suffix)}{$params.suffix}{/if}
					{if isset($params.color) && isset($tr.color)}
						</span>
					{/if}
					{if isset($params.badge_danger) && $params.badge_danger && isset($tr.badge_danger) && $tr.badge_danger == $params.badge_danger}</span>{/if}
					{if isset($params.badge_warning) && $params.badge_warning && isset($tr.badge_warning) && $tr.badge_warning == $params.badge_warning}</span>{/if}
					{if isset($params.badge_success) && $params.badge_success && isset($tr.badge_success) && $tr.badge_success == $params.badge_success}</span>{/if}
				{/block}
				{block name="close_td"}
					</td>
				{/block}
			{/foreach}

		{if isset($myParcel) && $myParcel == 'true'}
			{assign var = myParcelOrderData value = MyParcel::getOrderData($tr.$identifier)}

			<td class="myparcel_align_right">
				<table class="myparcel_table">
					<tr
						{if $position_identifier}id="tr_{$position_group_identifier}_{$tr.$identifier}_{if isset($tr.position['position'])}{$tr.position['position']}{else}0{/if}"{/if}
						class="{if isset($tr.class)} {$tr.class}{/if} {if $tr@iteration is odd by 1}odd{/if}"
						{if isset($tr.color) && $color_on_bg}style="background-color: {$tr.color}"{/if}
					>
						<td id="mypa_exist_{$tr.$identifier}">
							<div>
	       						<input type="checkbox" value="{$myParcelOrderData.checks}" id="mypa_check_{$tr.$identifier}" class="mypaleft mypacheck">
	       						{$myParcelOrderData.items}
	       					</div>
						</td>
						<td class="mypafunc">
							<a href="#" class="myparcel-consignment-new" onclick="return createNewConsignment('{$tr.$identifier}');"><img src="/modules/myparcel/images/myparcel_pdf_add.png" alt="New"></a>
							<a href="#" class="myparcel-consignment-retour" onclick="return createNewConsignment('{$tr.$identifier}', true);"><img src="/modules/myparcel/images/myparcel_retour_add.png" alt="Retour"></a>
						</td>
					</tr>
				</table>
			</td>
		{/if}

		{if $shop_link_type}
			<td title="{$tr.shop_name}">
				{if isset($tr.shop_short_name)}
					{$tr.shop_short_name}
				{else}
					{$tr.shop_name}
				{/if}
			</td>
		{/if}
		{if $has_actions}
			<td class="text-right">
				{assign var='compiled_actions' value=array()}
				{foreach $actions AS $key => $action}
					{if isset($tr.$action)}
						{if $key == 0}
							{assign var='action' value=$action}
						{/if}
						{$compiled_actions[] = $tr.$action}
					{/if}
				{/foreach}
				{if $compiled_actions|count > 0}
					{if $compiled_actions|count > 1}<div class="btn-group-action">{/if}
					<div class="btn-group pull-right">
						{$compiled_actions[0]|regex_replace:'/class\s*=\s*"(\w*)"/':'class="$1 btn btn-default"'}
						{if $compiled_actions|count > 1}
						<button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<i class="icon-caret-down"></i>&nbsp;
						</button>
							<ul class="dropdown-menu">
							{foreach $compiled_actions AS $key => $action}
								{if $key != 0}
								<li>
									{$action}
								</li>
								{/if}
							{/foreach}
							</ul>
						{/if}
					</div>
					{if $compiled_actions|count > 1}</div>{/if}
				{/if}
			</td>
		{/if}
		</tr>
	{/foreach}
	{else}
		<tr>
			<td class="list-empty" colspan="{count($fields_display) + 2}">
				<div class="list-empty-msg">
					<i class="icon-warning-sign list-empty-icon"></i>
					{l s='No records found'}
				</div>
			</td>
		</tr>
	{/if}
	</tbody>
{/if}
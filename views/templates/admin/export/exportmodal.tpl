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
<div id="myparcelExportModal">
	<div id="closebt-container" class="close-myparcelExportModal">
		<img class="closebt" src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/closebt.svg">
	</div>
	<div id="myparcel-bootstrap">
		<div class="container container-with-footer">
			<h1>Goedemorgen, meneer Dekker</h1>
			<h2>Hoe kunnen we u vandaag van dienst zien?</h2>

			<div id="myparcel-exportpanel"></div>
		</div>
		<div class="footer" style="padding-left: 20px; padding-right: 20px;">
			<div class="row">
				<div class="col-xs-1" style="margin-top: 40px;">
					<a href="#" class="btn btn-warning btn-lg pull-left myparcel-previous"><i class="icon icon-chevron-left"></i> <span>Vorige</span></a>
				</div>
				<div class="col-xs-10 bs-wizard" style="border-bottom:0;">
					<div class="col-xs-4 bs-wizard-step complete">
						<div class="text-center bs-wizard-stepnum">{l s='Information' mod='myparcel'}</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="#" class="bs-wizard-dot"></a>
					</div>
					<div class="col-xs-4 bs-wizard-step active">
						<div class="text-center bs-wizard-stepnum">{l s='Export' mod='myparcel'}</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="#" class="bs-wizard-dot"></a>
					</div>
					<div class="col-xs-4 bs-wizard-step disabled">
						<div class="text-center bs-wizard-stepnum">{l s='Print' mod='myparcel'}</div>
						<div class="progress"><div class="progress-bar"></div></div>
						<a href="#" class="bs-wizard-dot"></a>
					</div>
				</div>
				<div class="col-xs-1" style="margin-top: 40px;">
					<a href="#" class="btn btn-warning btn-lg pull-right myparcel-next"><span>Volgende</span> <i class="icon icon-chevron-right"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>

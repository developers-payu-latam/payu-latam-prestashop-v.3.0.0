{*
* 2016 PAYU LATAM
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
*  @author    PAYU LATAM <sac@payulatam.com>
*  @copyright 2014-2016 PAYU LATAM
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<link href="{$css|escape:'htmlall':'UTF-8'}normalize.css" rel="stylesheet" type="text/css">
<link href="{$css|escape:'htmlall':'UTF-8'}payu.css" rel="stylesheet" type="text/css">
<img src="{$tracking|escape:'htmlall':'UTF-8'}" alt="tracking" class="md-tracking"/>
<div class="ctwrapper">
	<div class="header_payu clearfix">
		<div class="left-container">
			<div class="md-copy_payu">
				<span class="logo-py"><img src="{$img|escape:'htmlall':'UTF-8'}logo.png" alt="logo"></span>
				{l s='Accept local payments' mod='payulatam'} <span class="tx-blue-ligth">{l s='on your website' mod='payulatam'}</span>
			</div>
			<a href="https://secure.payulatam.com/online_account/create_account.zul" class="md-btn button_en button_payu">{l s='Open your PayU Account' mod='payulatam'} </a>
		</div>
	</div>
	<div class="icons">
		<div class="md-icos_payu clearfix">
			<ul>
				<li><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-credito.png' mod='payulatam'}" alt="ico1"></li>
				<li><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-pago.png' mod='payulatam'}" alt="ico2"></li>
				<li><img src="{$img|escape:'htmlall':'UTF-8'}{l s='ico-trans.png' mod='payulatam'}" alt="ico3"></li>
			</ul>
		</div>
	</div>

	<div class="section_payu">
		<div class="container_payu clearfix">
			<div class="md-wrapper_payu clearfix">
				<div class="md-tl_payu md-col_payu">
					<h2>Pay<span class="tx-blue-ligth">U</span> {l s='solutions will help you to' mod='payulatam'} <span class="tx-blue-ligth">{l s='increase your online sales' mod='payulatam'}</span></h2>
					<p>{l s='PayU is the leading online payment service provider in Latin America with more than 20,000 clients. With more than 10 years of experience in the market, PayU has the most complete anti-fraud system in the region and offers the New Generation of Payment Solutions that allows its merchants to accept more than 70 payment options in Argentina, Brazil, Chile, Colombia, Mexico, Panama and Peru.' mod='payulatam'}</p>
				</div>
				<div class="payu_image">
					<img src="{$img|escape:'htmlall':'UTF-8'}{l s='graphic_prestashop.png' mod='payulatam'}" width="408" height="231" alt=" ">
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<div class="container_payu clearfix">
			<div class="md-col_payu benefits">
				<h3>{l s='Benefits' mod='payulatam'}</h3>
				<ul>
					<li>{l s='Accept different payment options in one platform: cash payments, credit cards (local and international) and bank transfers.' mod='payulatam'}</li>
					<li>{l s='With just one integration, you can receive payments in 7 countries in Latin America in local currency.' mod='payulatam'}</li>
					<li>{l s='Take advantage of the multi-language and multi-currency platform.' mod='payulatam'}</li>
					<li>{l s='Utilize the PayU Checkout, which has been optimized to increase the number of completed transactions.' mod='payulatam'}</li>
					<li>{l s='Avoid large investments in infrastructure, technological developments, maintenance and management of the payment system.' mod='payulatam'}</li>
				</ul>
			</div>
			<div class="md-col_payu security">
				<h3>{l s='Security and Recognition' mod='payulatam'}</h3>
				<ul>
					<li><b>{l s='Anti-Fraud Control:'  mod='payulatam'}</b> {l s='The PayU Anti-Fraud system automatically validates transactions and, when necessary, expert analysts manually verify transactions to minimize fraudulent transactions.' mod='payulatam'}</li>
					<li><b>{l s='PCI DSS Certification:'  mod='payulatam'}</b> {l s='With this certification, PayU adheres to its standards and ensures the cardholder will have the highest level of security, confidentiality and integrity.' mod='payulatam'}</li>
					<li><b>{l s='Veracode Recognition:'  mod='payulatam'}</b> {l s='PayU is the only Latin American company recognized for its high security standards in the development of its transactional platform and associated services.' mod='payulatam'}</li>
				</ul>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<div class="container_payu clearfix md_wrapper_gray">
		{foreach from=$tab item=div}
			<div id="{html_entity_decode($div.tab|escape:'htmlall':'UTF-8')}" class="{$div.style|escape:'htmlall':'UTF-8'}">
				{html_entity_decode($div.content|escape:'htmlall':'UTF-8')}
			</div>
		{/foreach}
		<div class="clear"></div>
	</div>
</div>
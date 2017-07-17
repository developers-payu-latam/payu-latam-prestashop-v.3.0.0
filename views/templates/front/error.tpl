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

<div class="error">
  {foreach from=$postErrors item=error}
     <img src="../img/admin/forbbiden.gif" alt="nok" />&nbsp;{$error|escape:'htmlall':'UTF-8'}</br>
  {/foreach}
</div>

<?php
/**
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
*/

include(dirname(__FILE__) . '/../../../config/config.inc.php');
include(dirname(__FILE__) . '/../../../init.php');
include(dirname(__FILE__) . '/../payulatam.php');
include(dirname(__FILE__) . '/../../../header.php');

$payulatam = new PayuLatam();

$signature = isset($_REQUEST['signature']) ? $_REQUEST['signature'] : $_REQUEST['firma'];

$merchant_id = isset($_REQUEST['merchantId']) ? $_REQUEST['merchantId'] : $_REQUEST['usuario_id'];

$reference_code = isset($_REQUEST['referenceCode']) ? $_REQUEST['referenceCode'] : $_REQUEST['ref_venta'];

$value = isset($_REQUEST['TX_VALUE']) ? $_REQUEST['TX_VALUE'] : $_REQUEST['valor'];

$currency = isset($_REQUEST['currency']) ? $_REQUEST['currency'] : $_REQUEST['moneda'];

$transaction_state = isset($_REQUEST['transactionState']) ? $_REQUEST['transactionState'] : $_REQUEST['estado'];

$value = number_format($value, 1, '.', '');

$api_key = Configuration::get('PAYU_LATAM_API_KEY');
$signature_local = $api_key . '~' . $merchant_id . '~' . $reference_code . '~' .
        $value . '~' . $currency . '~' . $transaction_state;
$signature_md5 = md5($signature_local);

$pol_response_code = isset($_REQUEST['polResponseCode']) ?
        $_REQUEST['polResponseCode'] : $_REQUEST['codigo_respuesta_pol'];

$messageApproved = '';
if ($transaction_state == 6 && $pol_response_code == 5) {
    $estado_tx = $payulatam->l('Failed Transaction');
} elseif ($transaction_state == 6 && $pol_response_code == 4) {
    $estado_tx = $payulatam->l('Rejected Transaction');
} elseif ($transaction_state == 12 && $pol_response_code == 9994) {
    $estado_tx = $payulatam->l('Pending Transaction, Please check if the debit was made in the Bank');
} elseif ($transaction_state == 4 && $pol_response_code == 1) {
    $estado_tx = $payulatam->l('Transaction Approved');
    $messageApproved = $payulatam->l('¡Thank you for your purchase!');
} else {
    $estado_tx = isset($_REQUEST['message']) ? $_REQUEST['message'] : $_REQUEST['mensaje'];
}

$transaction_id = isset($_REQUEST['transactionId']) ? $_REQUEST['transactionId'] : $_REQUEST['transaccion_id'];

$reference_pol = isset($_REQUEST['reference_pol']) ? $_REQUEST['reference_pol'] : $_REQUEST['ref_pol'];

$pse_bank = isset($_REQUEST['pseBank']) ? $_REQUEST['pseBank'] : $_REQUEST['banco_pse'];

$cus = $_REQUEST['cus'];

$description = isset($_REQUEST['description']) ? $_REQUEST['description'] : $_REQUEST['descripcion'];

$lap_payment_method = isset($_REQUEST['lapPaymentMethod']) ?
        $_REQUEST['lapPaymentMethod'] : $_REQUEST['medio_pago_lap'];

$cart = new Cart((int)$reference_code);

if (Tools::strtoupper($signature) == Tools::strtoupper($signature_md5)) {
    if (!($cart->orderExists())) {
        $customer = new Customer((int)$cart->id_customer);
        Context::getContext()->customer = $customer;
        
        $vrIdCart = (int)$cart->id;
        $vrStatus = Configuration::get('PAYU_OS_PENDING');
        $vrAmount = (float)$cart->getordertotal(true);
        $vrCurrency = (int)$cart->id_currency;
        $vrKey = $customer->secure_key;
        
        $payulatam->validateOrder($vrIdCart, $vrStatus, $vrAmount, 'PayU', null, array(), $vrCurrency, false, $vrKey);
    }

    Context::getContext()->smarty->assign(
        array(
            'estadoTx' => $estado_tx,
            'transactionId' => $transaction_id,
            'reference_pol' => $reference_pol,
            'referenceCode' => $reference_code,
            'pseBank' => $pse_bank,
            'cus' => $cus,
            'value' => $value,
            'currency' => $currency,
            'description' => $description,
            'lapPaymentMethod' => $lap_payment_method,
            'messageApproved' => $messageApproved,
            'valid' => true,
            'css' => '../modules/payulatam/css/'
        )
    );

} else {
    Context::getContext()->smarty->assign(
        array(
            'valid' => false,
            'css' => '../modules/payulatam/css/'
        )
    );
}
Context::getContext()->smarty->display(dirname(__FILE__) . '/../views/templates/front/response.tpl');
include(dirname(__FILE__) . '/../../../footer.php');

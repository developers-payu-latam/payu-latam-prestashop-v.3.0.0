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
 * @author    PAYU LATAM <sac@payulatam.com>
 * @copyright 2014-2016 PAYU LATAM
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

include(dirname(__FILE__) . '/../../../config/config.inc.php');
include(dirname(__FILE__) . '/../../../init.php');
include(dirname(__FILE__) . '/../payulatam.php');

$signature = isset($_REQUEST['sign']) ? $_REQUEST['sign'] : $_REQUEST['firma'];

$merchant_id = isset($_REQUEST['merchant_id']) ? $_REQUEST['merchant_id'] : $_REQUEST['usuario_id'];

$reference_code = isset($_REQUEST['reference_sale']) ? $_REQUEST['reference_sale'] : $_REQUEST['ref_venta'];

$value = isset($_REQUEST['value']) ? $_REQUEST['value'] : $_REQUEST['valor'];

$currency = isset($_REQUEST['currency']) ? $_REQUEST['currency'] : $_REQUEST['moneda'];

$transaction_state = isset($_REQUEST['state_pol']) ? $_REQUEST['state_pol'] : $_REQUEST['estado_pol'];


$split = explode('.', $value);
$decimals = $split[1];
if ($decimals % 10 == 0) {
    $value = number_format($value, 1, '.', '');
}

$payulatam = new PayuLatam();
$api_key = Configuration::get('PAYU_LATAM_API_KEY');
$signature_local = $api_key . '~' . $merchant_id . '~' . $reference_code . '~' .
        $value . '~' . $currency . '~' . $transaction_state;
$signature_md5 = md5($signature_local);

$pol_response_code = isset($_REQUEST['response_code_pol']) ? $_REQUEST['response_code_pol'] :
    $_REQUEST['codigo_respuesta_pol'];

$cart = new Cart((int)$reference_code);
if (Tools::strtoupper($signature) == Tools::strtoupper($signature_md5)) {
    $state = 'PAYU_OS_FAILED';
    $errors = [];
    if ($transaction_state == 6 && $pol_response_code == 5) {
        $state = 'PAYU_OS_FAILED';
    } elseif ($transaction_state == 6 && $pol_response_code == 4) {
        $state = 'PAYU_OS_REJECTED';
    } elseif ($transaction_state == 12 && $pol_response_code == 9994) {
        $state = 'PAYU_OS_PENDING';
    } elseif ($transaction_state == 4 && $pol_response_code == 1) {
        $state = 'PS_OS_PAYMENT';
    }

    if (!Validate::isLoadedObject($cart)) {
        $errors = Module::getInstanceByName('invalid cart id');
        echo $errors->l('Invalid Cart ID');
    } else {
        $currency_cart = new Currency((int)$cart->id_currency);
        if ($currency != $currency_cart->iso_code) {
            $errors = Module::getInstanceByName('invalid cart id');
            echo $errors->l('Invalid Currency ID') . ' ' . ($currency . '|' . $currency_cart->iso_code);
        } else {
            if ($cart->orderExists()) {
                $order = new Order((int)Order::getOrderByCartId($cart->id));

                if (_PS_VERSION_ < '1.5') {
                    $current_state = $order->getCurrentState();
                    if ($current_state != Configuration::get('PS_OS_PAYMENT')) {
                        $history = new OrderHistory();
                        $history->id_order = (int)$order->id;
                        $history->changeIdOrderState((int)Configuration::get($state), $order->id);
                        $history->addWithemail(true);
                    }
                } else {
                    $current_state = $order->current_state;
                    if ($current_state != Configuration::get('PS_OS_PAYMENT')) {
                        $history = new OrderHistory();
                        $history->id_order = (int)$order->id;
                        $history->changeIdOrderState((int)Configuration::get($state), $order, true);
                        $history->addWithemail(true);
                    }
                }
            } else {
                $customer = new Customer((int)$cart->id_customer);
                Context::getContext()->customer = $customer;
                Context::getContext()->currency = $currency_cart;
                
                $vcCrt = (int)$cart->id;
                $vcState = (int)Configuration::get($state);
                $vcAmount = (float)$cart->getordertotal(true);
                $vcCurr = (int)$currency_cart->id;
                $vcKey = $customer->secure_key;
                $vcStr = 'PayU Latam';
                $payulatam->validateOrder($vcCrt, $vcState, $vcAmount, $vcStr, null, array(), $vcCurr, false, $vcKey);
                Configuration::updateValue('PAYULATAM_CONFIGURATION_OK', true);
                $order = new Order((int)Order::getOrderByCartId($cart->id));
            }
            if ($state != 'PS_OS_PAYMENT') {
                foreach ($order->getProductsDetail() as $product) {
                    $product_id = $product['product_id'];
                    $product_attribute_id = $product['product_attribute_id'];
                    $product_quantity = +(int)$product['product_quantity'];
                    $id_shop = $order->id_shop;
                    StockAvailable::updateQuantity($product_id, $product_attribute_id, $product_quantity, $id_shop);
                }
            }
        }
    }
}

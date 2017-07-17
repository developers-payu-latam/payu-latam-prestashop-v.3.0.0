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

if (!defined('_PS_VERSION_')) {
    function exitPs()
    {
        exit;
    }
}

class PayuLatam extends PaymentModule
{

    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'payulatam';
        $this->tab = 'payments_gateways';
        $this->version = '2.1.2';
        $this->author = 'PayU Latam';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->module_key = '184ac5174295980fa7094d1b8a8578b5';

        parent::__construct();

        $this->displayName = $this->l('PayU Latam');
        $this->description = $this->l('Payment gateway for PayU Latam');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        /* Backward compatibility */
        if (_PS_VERSION_ < '1.5') {
            require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
        }
        $this->checkForUpdates();
    }
    
    public function install()
    {
        $this->createStates();

        if (!parent::install() || !$this->registerHook('payment') || !$this->registerHook('paymentReturn')) {
            return false;
        }
        return true;
        
    }
    
    public function uninstall()
    {
        if (!parent::uninstall()
            || !Configuration::deleteByName('PAYU_LATAM_MERCHANT_ID')
            || !Configuration::deleteByName('PAYU_LATAM_ACCOUNT_ID')
            || !Configuration::deleteByName('PAYU_LATAM_API_KEY')
            || !Configuration::deleteByName('PAYU_LATAM_TEST')
            || !Configuration::deleteByName('PAYU_OS_PENDING')
            || !Configuration::deleteByName('PAYU_OS_FAILED')
            || !Configuration::deleteByName('PAYU_OS_REJECTED')
        ) {
            return false;
        }
        return true;
    }

    public function getContent()
    {
        $html = '';
        if (!empty(Tools::getValue('submitPayU'))) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                    $this->saveConfiguration();
                    $html .= $this->displayConfirmation($this->l('Settings updated'));
            } else {
                foreach ($this->postErrors as $err) {
                    $html .= $this->displayError($err);
                }
            }
        }
        return $html.$this->displayAdminTpl();
    }

    private function displayAdminTpl()
    {
        $polUrl = 'http://www.prestashop.com/modules/pagosonline.png?url_site=';
        $this->context->smarty->assign(array(
            'tab' => array(
                'intro' => array(
                    'title' => $this->l('How to configure'),
                    'content' => $this->displayHelpTpl(),
                    'icon' => '../modules/payulatam/views/img/info-icon.gif',
                    'tab' => 'conf',
                    'selected' => (Tools::isSubmit('submitPayU') ? false : true),
                    'style' => 'config_payu'
                    ),
                'credential' => array(
                    'title' => $this->l('Credentials'),
                    'content' => $this->displayCredentialTpl(),
                    'icon' => '../modules/payulatam/views/img/credential.png',
                    'tab' => 'crendeciales',
                    'selected' => (Tools::isSubmit('submitPayU') ? true : false),
                    'style' => 'credentials_payu'
                ),
            ),
            'tracking' => $polUrl . Tools::safeOutput($_SERVER['SERVER_NAME']) . '&id_lang=' .
                (int)$this->context->cookie->id_lang,
            'img' => '../modules/payulatam/views/img/',
            'css' => '../modules/payulatam/views/css/',
            'lang' => ($this->context->language->iso_code != 'en' ||
                        $this->context->language->iso_code != 'es' ? 'en' : $this->context->language->iso_code)
        ));

        return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
    }

    private function displayHelpTpl()
    {
        return $this->display(__FILE__, 'views/templates/admin/help.tpl');
    }
    
    private function displayCredentialTpl()
    {
        $this->context->smarty->assign(array(
            'formCredential' => './index.php?tab=AdminModules&configure=payulatam&token=' .
            Tools::getAdminTokenLite('AdminModules') .
            '&tab_module=' . $this->tab . '&module_name=payulatam',
            'credentialTitle' => $this->l('Log in'),
            'credentialInputVar' => array(
                'merchant_id' => array(
                    'name' => 'merchant_id',
                    'required' => true,
                    'value' => (Tools::getValue('merchant_id') ? Tools::safeOutput(Tools::getValue('merchant_id')) :
                        Tools::safeOutput(Configuration::get('PAYU_LATAM_MERCHANT_ID'))),
                    'type' => 'text',
                    'label' => $this->l('Merchant'),
                    'desc' => $this->l('You will find the Merchant ID in the section Technical Information') .
                    $this->l(' of the Administrative Module.'),
                ),
                'api_key' => array(
                    'name' => 'api_key',
                    'required' => true,
                    'value' => (Tools::getValue('api_key') ? Tools::safeOutput(Tools::getValue('api_key')) :
                        Tools::safeOutput(Configuration::get('PAYU_LATAM_API_KEY'))),
                    'type' => 'text',
                    'label' => $this->l('Api Key'),
                    'desc' => $this->l('You will find the API Key in the section Technical Information') .
                    $this->l(' of the Administrative Module.'),
                ),
                'account_id' => array(
                    'name' => 'account_id',
                    'required' => false,
                    'value' => (Tools::getValue('account_id') ? (int)Tools::getValue('account_id') :
                        (int)Configuration::get('PAYU_LATAM_ACCOUNT_ID')),
                    'type' => 'text',
                    'label' => $this->l('Account ID'),
                    'desc' => $this->l('You will find the Account ID in the section Account') .
                    $this->l(' of the Administrative Module.'),
                ),
                'test' => array(
                    'name' => 'test',
                    'required' => false,
                    'value' => (Tools::getValue('test') ? Tools::safeOutput(Tools::getValue('test')) :
                        Tools::safeOutput(Configuration::get('PAYU_LATAM_TEST'))),
                    'type' => 'radio',
                    'values' => array('true', 'false'),
                    'label' => $this->l('Mode Test'),
                    'desc' => $this->l(''),
                ))));
        return $this->display(__FILE__, 'views/templates/admin/credential.tpl');
    }

    public function hookPayment($params)
    {
        if ($this->active) {
        } else {
            return;
        }
        
        $this->context->smarty->assign(array(
            'css' => 'modules/' . $this->name . '/views/css/',
            'module_dir' => _PS_MODULE_DIR_ . $this->name . '/'
        ));

        return $this->display(__FILE__, 'views/templates/hook/payulatam_payment.tpl');
    }
    
    private function postValidation()
    {
        if (!Validate::isCleanHtml(Tools::getValue('merchant_id')) ||
                !Validate::isGenericName(Tools::getValue('merchant_id'))) {
            $this->postErrors[] = $this->l('You must indicate the merchant id');
        }

        if (!Validate::isCleanHtml(Tools::getValue('account_id')) ||
                !Validate::isGenericName(Tools::getValue('account_id'))) {
            $this->postErrors[] = $this->l('You must indicate the account id');
        }

        if (!Validate::isCleanHtml(Tools::getValue('api_key')) ||
                !Validate::isGenericName(Tools::getValue('api_key'))) {
            $this->postErrors[] = $this->l('You must indicate the API key');
        }

        if (!Validate::isCleanHtml(Tools::getValue('test')) ||
                !Validate::isGenericName(Tools::getValue('test'))) {
            $this->postErrors[] = $this->l('You must indicate if the transaction mode is test or not');
        }

    }

    private function saveConfiguration()
    {
        Configuration::updateValue('PAYU_LATAM_MERCHANT_ID', (string)Tools::getValue('merchant_id'));
        Configuration::updateValue('PAYU_LATAM_ACCOUNT_ID', (string)Tools::getValue('account_id'));
        Configuration::updateValue('PAYU_LATAM_API_KEY', (string)Tools::getValue('api_key'));
        Configuration::updateValue('PAYU_LATAM_TEST', Tools::getValue('test'));
    }
    
    private function createStates()
    {
        if (!Configuration::get('PAYU_OS_PENDING')) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Pending';
            }
            
            $order_state->send_email = false;
            $order_state->color = '#FEFF64';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;
            
            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/logo.png';
                $destination = dirname(__FILE__) . '/../../views/img/os/' . (int)$order_state->id . '.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('PAYU_OS_PENDING', (int)$order_state->id);
        }

        if (!Configuration::get('PAYU_OS_FAILED')) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Failed Payment';
            }

            $order_state->send_email = false;
            $order_state->color = '#8F0621';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/logo.png';
                $destination = dirname(__FILE__) . '/../../views/img/os/' . (int)$order_state->id . '.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('PAYU_OS_FAILED', (int)$order_state->id);
        }

        if (!Configuration::get('PAYU_OS_REJECTED')) {
            $order_state = new OrderState();
            $order_state->name = array();
            foreach (Language::getLanguages() as $language) {
                $order_state->name[$language['id_lang']] = 'Rejected Payment';
            }

            $order_state->send_email = false;
            $order_state->color = '#8F0621';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = false;
            $order_state->invoice = false;

            if ($order_state->add()) {
                $source = dirname(__FILE__) . '/views/img/logo.png';
                $destination = dirname(__FILE__) . '/../../views/img/os/' . (int)$order_state->id . '.gif';
                copy($source, $destination);
            }
            Configuration::updateValue('PAYU_OS_REJECTED', (int)$order_state->id);
        }
    }
    
    private function checkForUpdates()
    {
        // Used by PrestaShop 1.3 & 1.4
        if (version_compare(_PS_VERSION_, '1.5', '<') && self::isInstalled($this->name)) {
            foreach (array('2.0') as $version) {
                $file = dirname(__FILE__) . '/upgrade/upgrade-' . $version . '.php';
                if (Configuration::get('PAYU_LATAM') < $version && file_exists($file)) {
                    include_once($file);
                    call_user_func('upgrade_module_' . str_replace('.', '_', $version), $this);
                }
            }
        }
    }
}

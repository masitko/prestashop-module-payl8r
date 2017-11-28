<?php

/**
 * Prestashop Payl8r payment module
 * 
 * @author Marek Sitko (email masitko@gmail.com)
 * 
 */


if (!defined('_PS_VERSION_')) {
  exit;
}
if (class_exists('MyLogPHP') != true) {
  include_once(_PS_MODULE_DIR_ . 'payplug/classes/MyLogPHP.class.php');
}

class Payl8r extends PaymentModule
{
  protected $_html = '';
  protected $_postErrors = array();
  protected $log;

  public $details;
  public $owner;
  public $address;
  public $extra_mail_vars;

  public function __construct()
  {
    $this->name = 'payl8r';
    $this->tab = 'payments_gateways';
    $this->version = '1.0.0';
    $this->author = 'Marek Sitko';
    $this->need_instance = true;
    $this->bootstrap = true;
    $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    $this->author = 'Marek Sitko';
    $this->currencies = true;
        // $this->controllers = array('payment', 'validation');
        // $this->limited_countries = array('gb');
        
        // $this->is_eu_compatible = 1;


        // $this->currencies_mode = 'checkbox';

    parent::__construct();
        

        // $this->log = new MyLogPHP(_PS_MODULE_DIR_.$this->name.'/log/debug.csv');
        // $this->log->info('Starting installation.');

    $this->meta_title = $this->l('Payl8r');
    $this->displayName = $this->l('Payl8r payments');
    $this->description = $this->l("Payl8r lets you buy the products you want today. With our flexible repayment plans, you decide when and how to pay back. Unlike credit and debit cards, we don't charge extortionate fees if you don't pay back on time.");
    $this->confirmUninstall = $this->l('Are you sure about removing these details?');
    if (!Configuration::get('PS_SSL_ENABLED')) {
      $this->warning = $this->l('You must enable SSL on the store if you want to use this module');
    }
    if (!$this->active) {
      $this->warning = $this->l('Payl8r not active!');
    }

  }

  public function install()
  {
        // Install default
    if (!parent::install()) {
      return false;
    }

    if (!$this->registrationHooks()) {
      return false;
    }

        // if (!$this->installOrderState()) {
        //     return false;
        // }

    if (!Configuration::updateValue('PAYL8R_USERNAME', '')
      || !Configuration::updateValue('PAYL8R_MERCHANT_KEY', '')
      || !Configuration::updateValue('PAYL8R_SANDBOX', 0)
      || !Configuration::updateValue('PAYL8R_MIN_VALUE', 0)) {
      return false;
    }
    
        // Configuration::updateValue(self::FLAG_DISPLAY_PAYMENT_INVITE, true);
        // if (!parent::install() || !$this->registerHook('paymentReturn') || !$this->registerHook('paymentOptions')) {
        //     return false;
        // }
    return true;
  }

  public function uninstall()
  {
    if (!Configuration::deleteByName('PAYL8R_USERNAME')
      || !Configuration::deleteByName('PAYL8R_MERCHANT_KEY')
      || !Configuration::deleteByName('PAYL8R_SANDBOX')
      || !Configuration::deleteByName('PAYL8R_MIN_VALUE')
      || !parent::uninstall()) {
      return false;
    }
    return true;
  }

  private function registrationHooks()
  {
    if (!$this->registerHook('payment')
      || !$this->registerHook('paymentReturn')
      || !$this->registerHook('displayPaymentEU')
        //     || !$this->registerHook('displayOrderConfirmation')
        //     || !$this->registerHook('displayAdminOrder')
        //     || !$this->registerHook('actionOrderStatusPostUpdate')
        //     || !$this->registerHook('actionValidateOrder')
        //     || !$this->registerHook('actionOrderStatusUpdate')
    ) {
      return false;
    }


    return true;
  }


  protected function _postValidation()
  {
    if (Tools::isSubmit('btnSubmit')) {

      if (!Tools::getValue('PAYL8R_USERNAME') || !Tools::getValue('PAYL8R_MERCHANT_KEY')) {
        $this->_postErrors[] = $this->l('Account details are required.');
      }
      if (Tools::getValue('PAYL8R_MIN_VALUE') && !is_numeric(Tools::getValue('PAYL8R_MIN_VALUE'))) {
        $this->_postErrors[] = $this->l('PLease enter a valid number as minimum value!');
      }
    }
  }

  protected function _postProcess()
  {
    if (Tools::isSubmit('btnSubmit')) {
      Configuration::updateValue('PAYL8R_USERNAME', Tools::getValue('PAYL8R_USERNAME'));
      Configuration::updateValue('PAYL8R_MERCHANT_KEY', Tools::getValue('PAYL8R_MERCHANT_KEY'));
      Configuration::updateValue('PAYL8R_SANDBOX', Tools::getValue('PAYL8R_SANDBOX'));
      Configuration::updateValue('PAYL8R_MIN_VALUE', Tools::getValue('PAYL8R_MIN_VALUE'));
    }
    $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
  }

  protected function _displayHeader()
  {
    return $this->display(__FILE__, 'infos.tpl');
  }

  public function getContent()
  {
    if (Tools::isSubmit('btnSubmit')) {
      $this->_postValidation();
      if (!count($this->_postErrors)) {
        $this->_postProcess();
      } else {
        foreach ($this->_postErrors as $err) {
          $this->_html .= $this->displayError($err);
        }
      }
    } else {
      $this->_html .= '<br />';
    }

    $this->_html .= $this->_displayHeader();
    $this->_html .= $this->renderAdminForm();

    return $this->_html;
  }

  public function hookPayment($params)
  {
    if (!$this->active) {
      return;
    }
    if (!$this->checkCurrency($params['cart'])) {
      return;
    }

    if (!$this->checkAmount($params['cart'])) {
      return;
    }

    $this->smarty->assign(array(
      'this_path' => $this->_path,
      'this_path_payl8r' => $this->_path,
      'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
    ));

    return $this->display(__FILE__, 'payment.tpl');
        
        // $newOption = new PaymentOption();
        // $newOption->setModuleName($this->name)
        //     ->setCallToActionText($this->l('Pay by '))
        //     ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/payl8rlogo.png'))
        //     ->setAction($this->context->link->getModuleLink($this->name, 'dispatcher', array(), true))
        //     // ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
        //     ->setAdditionalInformation($this->fetch('module:ps_wirepayment/views/templates/hook/ps_wirepayment_intro.tpl'));
    $payment_options = [];

    return $payment_options;
  }

  public function checkCurrency($cart)
  {
    $currency_order = new Currency($cart->id_currency);
    $currencies_module = $this->getCurrency($cart->id_currency);

    if (is_array($currencies_module)) {
      foreach ($currencies_module as $currency_module) {
        if ($currency_order->id == $currency_module['id_currency']) {
          return true;
        }
      }
    }
    return false;
  }

  public function checkAmount($cart)
  {
    $minAmount = floatval(Configuration::get('PAYL8R_MIN_VALUE'));
    $amount = $cart->getOrderTotal(true, Cart::BOTH);
    return $amount >= $minAmount;
  }


  public function hookDisplayPaymentEU($params)
  {
    if (!$this->active)
      return;

    if (!$this->checkCurrency($params['cart']))
      return;

    $payment_options = array(
      'cta_text' => $this->l('Pay by Bank Wire'),
      'logo' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/bankwire.jpg'),
      'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true)
    );

    return $payment_options;
  }


  public function hookPaymentReturn($params)
  {
    if (!$this->active) {
      return;
    }

    $state = $params['order']->getCurrentState();
    if (in_array(
      $state,
      array(
        Configuration::get('PS_OS_BANKWIRE'),
        Configuration::get('PS_OS_OUTOFSTOCK'),
        Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'),
      )
    )) {
      $bankwireOwner = $this->owner;
      if (!$bankwireOwner) {
        $bankwireOwner = '___________';
      }

      $bankwireDetails = Tools::nl2br($this->details);
      if (!$bankwireDetails) {
        $bankwireDetails = '___________';
      }

      $bankwireAddress = Tools::nl2br($this->address);
      if (!$bankwireAddress) {
        $bankwireAddress = '___________';
      }

      $this->smarty->assign(array(
        'shop_name' => $this->context->shop->name,
        'total' => Tools::displayPrice(
          $params['order']->getOrdersTotalPaid(),
          new Currency($params['order']->id_currency),
          false
        ),
        'bankwireDetails' => $bankwireDetails,
        'bankwireAddress' => $bankwireAddress,
        'bankwireOwner' => $bankwireOwner,
        'status' => 'ok',
        'reference' => $params['order']->reference,
        'contact_url' => $this->context->link->getPageLink('contact', true)
      ));
    } else {
      $this->smarty->assign(
        array(
          'status' => 'failed',
          'contact_url' => $this->context->link->getPageLink('contact', true),
        )
      );
    }

    return $this->fetch('module:ps_wirepayment/views/templates/hook/payment_return.tpl');
  }

  public function renderAdminForm()
  {
    $fields_form = array(
      'form' => array(
        'legend' => array(
          'title' => $this->l('Account details'),
          'icon' => 'icon-envelope'
        ),
        'input' => array(
          array(
            'type' => 'text',
            'label' => $this->l('Merchant username'),
            'name' => 'PAYL8R_USERNAME',
            'required' => true
          ),
          array(
            'type' => 'textarea',
            'label' => $this->l('Merchant public key'),
            'name' => 'PAYL8R_MERCHANT_KEY',
            'required' => true
          ),
        ),
        'submit' => array(
          'title' => $this->l('Save'),
        )
      ),
    );
    $fields_form_customization = array(
      'form' => array(
        'legend' => array(
          'title' => $this->l('Customization'),
          'icon' => 'icon-cogs'
        ),
        'input' => array(
          array(
            'type' => 'switch',
            'label' => $this->l('Test Mode (Sandbox)'),
            'name' => 'PAYL8R_SANDBOX',
            'is_bool' => true,
                    // 'hint' => $this->l('Your country\'s legislation may require you to send the invitation to pay by email only. Disabling the option will hide the invitation on the confirmation page.'),
            'values' => array(
              array(
                'id' => 'active_on',
                'value' => true,
                'label' => $this->l('Enabled'),
              ),
              array(
                'id' => 'active_off',
                'value' => false,
                'label' => $this->l('Disabled'),
              )
            ),
          ),
          array(
            'type' => 'text',
            'label' => $this->l('Minimum order value'),
            'desc' => $this->l('Please specify minimum order value in pounds.'),
            'name' => 'PAYL8R_MIN_VALUE',
          ),
        ),
        'submit' => array(
          'title' => $this->l('Save'),
        )
      ),
    );

    $helper = new HelperForm();
    $helper->show_toolbar = false;
    $helper->table = $this->table;
    $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
    $helper->default_form_language = $lang->id;
    $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
    $this->fields_form = array();
    $helper->id = (int)Tools::getValue('id_carrier');
    $helper->identifier = $this->identifier;
    $helper->submit_action = 'btnSubmit';
    $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure='
      . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->tpl_vars = array(
      'fields_value' => $this->getConfigFieldsValues(),
      'languages' => $this->context->controller->getLanguages(),
      'id_language' => $this->context->language->id
    );

    return $helper->generateForm(array($fields_form, $fields_form_customization));
  }

  public function getConfigFieldsValues()
  {
    return array(
      'PAYL8R_USERNAME' => Tools::getValue('PAYL8R_USERNAME', Configuration::get('PAYL8R_USERNAME')),
      'PAYL8R_MERCHANT_KEY' => Tools::getValue('PAYL8R_MERCHANT_KEY', Configuration::get('PAYL8R_MERCHANT_KEY')),
      'PAYL8R_SANDBOX' => Tools::getValue('PAYL8R_SANDBOX', Configuration::get('PAYL8R_SANDBOX')),
      'PAYL8R_MIN_VALUE' => Tools::getValue('PAYL8R_MIN_VALUE', Configuration::get('PAYL8R_MIN_VALUE')),
    );
  }

  public function getTemplateVarInfos()
  {
    $cart = $this->context->cart;
    $total = sprintf(
      $this->l('%1$s (tax incl.)'),
      Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH))
    );

    $bankwireOwner = $this->owner;
    if (!$bankwireOwner) {
      $bankwireOwner = '___________';
    }

    $bankwireDetails = Tools::nl2br($this->details);
    if (!$bankwireDetails) {
      $bankwireDetails = '___________';
    }

    $bankwireAddress = Tools::nl2br($this->address);
    if (!$bankwireAddress) {
      $bankwireAddress = '___________';
    }

    $bankwireReservationDays = Configuration::get('BANK_WIRE_RESERVATION_DAYS');
    if (false === $bankwireReservationDays) {
      $bankwireReservationDays = 7;
    }

    $bankwireCustomText = Tools::nl2br(Configuration::get('BANK_WIRE_CUSTOM_TEXT', $this->context->language->id));
    if (false === $bankwireCustomText) {
      $bankwireCustomText = '';
    }

    return array(
      'total' => $total,
      'bankwireDetails' => $bankwireDetails,
      'bankwireAddress' => $bankwireAddress,
      'bankwireOwner' => $bankwireOwner,
      'bankwireReservationDays' => (int)$bankwireReservationDays,
      'bankwireCustomText' => $bankwireCustomText,
    );
  }
}

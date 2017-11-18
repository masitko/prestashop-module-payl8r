<?php

/**
 * Pstashop Payl8r payment module
 * 
 * @author Marek Sitko (email masitko@gmail.com)
 * 
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Payl8r extends PaymentModule
{
    const FLAG_DISPLAY_PAYMENT_INVITE = 'BANK_WIRE_PAYMENT_INVITE';

    protected $_html = '';
    protected $_postErrors = array();

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
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Marek Sitko';
        $this->currencies = true;
        // $this->controllers = array('payment', 'validation');
        $this->limited_countries = array('gb');
        
        // $this->is_eu_compatible = 1;


        // $this->currencies_mode = 'checkbox';

        parent::__construct();

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
        if (!$this->registerHook('paymentOptions')
        //     || !$this->registerHook('paymentReturn')
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
            Configuration::updateValue(
                self::FLAG_DISPLAY_PAYMENT_INVITE,
                Tools::getValue(self::FLAG_DISPLAY_PAYMENT_INVITE)
            );

            if (!Tools::getValue('BANK_WIRE_DETAILS')) {
                $this->_postErrors[] = $this->trans('Account details are required.', array(), 'Modules.Wirepayment.Admin');
            } elseif (!Tools::getValue('BANK_WIRE_OWNER')) {
                $this->_postErrors[] = $this->trans('Account owner is required.', array(), "Modules.Wirepayment.Admin");
            }
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('BANK_WIRE_DETAILS', Tools::getValue('BANK_WIRE_DETAILS'));
            Configuration::updateValue('BANK_WIRE_OWNER', Tools::getValue('BANK_WIRE_OWNER'));
            Configuration::updateValue('BANK_WIRE_ADDRESS', Tools::getValue('BANK_WIRE_ADDRESS'));

            $custom_text = array();
            $languages = Language::getLanguages(false);
            foreach ($languages as $lang) {
                if (Tools::getIsset('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang'])) {
                    $custom_text[$lang['id_lang']] = Tools::getValue('BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang']);
                }
            }
            Configuration::updateValue('BANK_WIRE_RESERVATION_DAYS', Tools::getValue('BANK_WIRE_RESERVATION_DAYS'));
            Configuration::updateValue('BANK_WIRE_CUSTOM_TEXT', $custom_text);
        }
        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
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
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(
            $this->getTemplateVarInfos()
        );

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
            ->setCallToActionText($this->trans('Pay by bank wire', array(), 'Modules.Wirepayment.Shop'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setAdditionalInformation($this->fetch('module:ps_wirepayment/views/templates/hook/ps_wirepayment_intro.tpl'));
        $payment_options = [
            $newOption,
        ];

        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active || !Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)) {
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

    public function renderForm()
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
                array(
                    'type' => 'switch',
                    'label' => $this->trans('Display the invitation to pay in the order confirmation page', array(), 'Modules.Wirepayment.Admin'),
                    'name' => self::FLAG_DISPLAY_PAYMENT_INVITE,
                    'is_bool' => true,
                    'hint' => $this->trans('Your country\'s legislation may require you to send the invitation to pay by email only. Disabling the option will hide the invitation on the confirmation page.', array(), 'Modules.Wirepayment.Admin'),
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->trans('Enabled', array(), 'Admin.Global'),
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->trans('Disabled', array(), 'Admin.Global'),
                        )
                    ),
                ),
            'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->trans('Reservation period', array(), 'Modules.Wirepayment.Admin'),
                        'desc' => $this->trans('Number of days the items remain reserved', array(), 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_RESERVATION_DAYS',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => $this->trans('Information to the customer', array(), 'Modules.Wirepayment.Admin'),
                        'name' => 'BANK_WIRE_CUSTOM_TEXT',
                        'desc' => $this->trans('Information on the bank transfer (processing time, starting of the shipping...)', array(), 'Modules.Wirepayment.Admin'),
                        'lang' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Save', array(), 'Admin.Actions'),
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
        $custom_text = array();
        $languages = Language::getLanguages(false);
        foreach ($languages as $lang) {
            $custom_text[$lang['id_lang']] = Tools::getValue(
                'BANK_WIRE_CUSTOM_TEXT_' . $lang['id_lang'],
                Configuration::get('BANK_WIRE_CUSTOM_TEXT', $lang['id_lang'])
            );
        }

        return array(
            'BANK_WIRE_DETAILS' => Tools::getValue('BANK_WIRE_DETAILS', Configuration::get('BANK_WIRE_DETAILS')),
            'BANK_WIRE_OWNER' => Tools::getValue('BANK_WIRE_OWNER', Configuration::get('BANK_WIRE_OWNER')),
            'BANK_WIRE_ADDRESS' => Tools::getValue('BANK_WIRE_ADDRESS', Configuration::get('BANK_WIRE_ADDRESS')),
            'BANK_WIRE_RESERVATION_DAYS' => Tools::getValue('BANK_WIRE_RESERVATION_DAYS', Configuration::get('BANK_WIRE_RESERVATION_DAYS')),
            'BANK_WIRE_CUSTOM_TEXT' => $custom_text,
            self::FLAG_DISPLAY_PAYMENT_INVITE => Tools::getValue(
                self::FLAG_DISPLAY_PAYMENT_INVITE,
                Configuration::get(self::FLAG_DISPLAY_PAYMENT_INVITE)
            )
        );
    }

    public function getTemplateVarInfos()
    {
        $cart = $this->context->cart;
        $total = sprintf(
            $this->trans('%1$s (tax incl.)', array(), 'Modules.Wirepayment.Shop'),
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

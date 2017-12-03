<?php

class Payl8rAboutModuleFrontController extends ModuleFrontController
{

	public $display_column_left = false;
	public $display_column_right = false;
	
	public function initContent()
	{
		parent::initContent();

    $this->context->controller->addCSS(_MODULE_DIR_ . 'payl8r/views/css/style.css');
    $this->context->controller->addCSS(_MODULE_DIR_ . 'payl8r/views/css/pl-calculator.css');
    $this->context->controller->addJS(_MODULE_DIR_ . 'payl8r/views/js/pl-calculator.js');
		
		$this->context->smarty->assign(array(
      'this_path_payl8r' => _MODULE_DIR_.'payl8r/',
		));
		
		$this->setTemplate('about.tpl');
  }
  
}

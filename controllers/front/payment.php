<?php

class Payl8rPaymentModuleFrontController extends ModuleFrontController
{
	public $ssl = true;
	public $display_column_left = false;
	public $display_column_right = false;
	
	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');

			
		$data = $this->prepareRequest($cart, $this->context->customer );

		$this->context->smarty->assign( $data );

		$this->setTemplate('payment_execution.tpl');
	}

	protected function prepareRequest($cart, $customer) {

		$username = Configuration::get('PAYL8R_USERNAME');
		$publicKey = Configuration::get('PAYL8R_MERCHANT_KEY');
		$test = Configuration::get('PAYL8R_SANDBOX');
		$products = $cart->getProducts(true);
		$moduleName = Tools::getValue('module');
		

    $product_description = implode("<br>", array_map( function($product) {
			return $product['name'];
		}, $products));
    if (strlen($product_description) > 79) {
      $product_description = 'Your order of ' . count($products) . ' items.';
    }		

    $abortUrl = $this->context->link->getModuleLink($moduleName, 'validation', array('status'=>'abort'), true);
    $failUrl = $this->context->link->getModuleLink($moduleName, 'validation', array('status'=>'fail'), true);
    $successUrl = $this->context->link->getModuleLink($moduleName, 'validation', array('status'=>'success'), true);
    $returnUrl = $this->context->link->getModuleLink($moduleName, 'response', array(), true);
		
		$address_delivery = new Address($cart->id_address_delivery);
		$address_billing = new Address($cart->id_address_invoice);
		
    $data = array(
      "username" => $username,
      "request_data" => array(
        "return_urls" => array(
          "abort" => str_replace('http:', 'https:', $abortUrl),
          "fail" => str_replace('http:', 'https:', $failUrl),
          "success" => str_replace('http:', 'https:', $successUrl),
          "return_data" => str_replace('http:', 'https:', $returnUrl),
        ),
        "request_type" => "standard_finance_request",
        "test_mode" => (int)$test,
        "order_details" => array(
          "order_id" => (string)$cart->id,
          "description" => $product_description,
          "currency" => "GBP",
          "total" => floatval($cart->getOrderTotal())
        ),
        "customer_details" => array(
          "student" => 0,

					"firstnames" => $customer->firstname,
          "surname" => $customer->lastname,
          "email" => $customer->email,
					// "dob" => $customer->birthday,
					
          "phone" => $address_billing->phone_mobile,
          "address" => $address_billing->address1 . ',' . $address_billing->address2,
          "city" => $address_billing->city,
          "postcode" => $address_billing->postcode,
          "country" => "UK",
        )
      )
    );

		$json_data = json_encode($data);
    openssl_public_encrypt($json_data, $crypted, $publicKey);

    return array(
      'rid' => $username,
      'data' => base64_encode($crypted),
			'action' => 'https://payl8r.com/process',
			'nbProducts' => count($products)
    );

	}

}

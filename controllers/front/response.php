<?php

class Payl8rResponseModuleFrontController extends ModuleFrontController
{

	public $display_column_left = false;
	public $display_column_right = false;
	
	public function postProcess()
	{
		$publicKey = Configuration::get('PAYL8R_MERCHANT_KEY');
    $response = Tools::getValue('response');    
    
    if ($encrypted_response = base64_decode($response)) {
      if (openssl_public_decrypt($encrypted_response, $json_response, $publicKey)) {
        if ($decoded_response = json_decode($json_response)) {
          if (isset($decoded_response->return_data)) {
            if ($decoded_response->return_data->order_id != '') {
              $this->processResponse($decoded_response->return_data);
              echo 'OK';
            }
          }
        }
      }
    }
    return;

		if( $status === 'success' ) {
			$this->module->validateOrder($cart->id, Configuration::get('PAYL8R_OS_PENDING'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
		}
		else {
			return $this->setTemplate('order-fail.tpl');
			
			// Tools::redirect('index.php?controller=order&step=3');
		}
  }
  
  protected function processResponse( $response ) {
    // Mage::log( 'SERVER RESPONSE: '.json_encode($response), null, 'payl8r.log', true );
    // $order = Mage::getModel('sales/order')->loadByIncrementId($response->order_id);
    $order = Order::getOrderByCartId($response->order_id);
    
    switch( $response->status  ) {
      case 'ACCEPTED':
        $order->setState( Mage_Sales_Model_Order::STATE_PROCESSING, 'payl8r_accepted', 'Payment Successful', true );
        $order->save();
        try {
          $order->queueNewOrderEmail();
        } catch (Exception $e) {}
        break;
      case 'DECLINED':
        $order->setState(  Mage_Sales_Model_Order::STATE_CANCELED, 'payl8r_declined', $response->reason, false );
        $order->save();
        break;
      case 'ABANDONED':
      default:
        $order->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, true);
        $order->setState(  Mage_Sales_Model_Order::STATE_CANCELED, 'payl8r_abandoned', $response->reason, false );
        $order->cancel()->save();
        break;
    }


    Mage::log( 'ORDER STATE: '.$order->getState(), null, 'payl8r.log', true );
    Mage::log( 'ORDER STATUS: '.$order->getStatus(), null, 'payl8r.log', true );

  }
  
}

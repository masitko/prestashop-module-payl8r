<?php
class Payl8rResponseModuleFrontController extends ModuleFrontController
{

  public $display_column_left = false;
  public $display_column_right = false;

  public function postProcess()
  {
    // $cart_id = Tools::getValue('cart_id');
    // $id_order = Order::getOrderByCartId((int)$cart_id);
    // $order = new Order($id_order);

    // var_dump($id_order);
    // var_dump($order);
    $publicKey = Configuration::get('PAYL8R_MERCHANT_KEY');
    $response = Tools::getValue('response');
    if (!$response) {
      die();
    }

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
  }

  protected function processResponse($response)
  {
    $cart_id = (int)$response->order_id;
    $id_order = Order::getOrderByCartId((int)$cart_id);
    if (!$id_order) {
      sleep(10);
      $id_order = Order::getOrderByCartId((int)$cart_id);
      if (!$id_order) {
        die();
      }
    }

    $order = new Order($id_order);
    $history = new OrderHistory();
    $history->id_order = (int)$order->id;

    switch ($response->status) {
      case 'ACCEPTED':
        $history->changeIdOrderState((int)Configuration::get('PS_OS_PAYMENT'), (int) ($order->id));
        $history->addWithemail();
        break;
      case 'DECLINED':
      case 'ABANDONED':
      default:
        $history->changeIdOrderState((int)Configuration::get('PS_OS_ERROR'), (int) ($order->id));
        $history->add();
        break;
    }

  }

}

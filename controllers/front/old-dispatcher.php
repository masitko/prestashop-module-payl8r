<?php

class Payl8rDispatcherModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     *
     * @return string
     */
    public function postProcess()
    {

        Tools::redirect('index.php?controller=order&step=3');
        
        if ((int)Tools::getValue('disp') == 1) {
            if ((int)Tools::getValue('pay') == 1) {
                if (Tools::getValue('pc') != 'new_card') {
                    $payplug = new Payplug();
                    $id_cart = (int)Tools::getValue('id_cart');
                    $id_card = Tools::getValue('pc');
                    $payment = $payplug->preparePayment($id_cart, $id_card);
                    if ($payment['result'] == true) {
                        Tools::redirect(
                            $this->context->link->getModuleLink(
                                'payplug',
                                'validation',
                                array('cartid' => $id_cart, 'ps' => 1),
                                true
                            )
                        );
                    } else {
                        Tools::redirect('index.php?controller=order&step=3&error=1&pc='.$id_card);
                    }
                } elseif ((int)Tools::getValue('lightbox') == 1) {
                    Tools::redirect('index.php?controller=order&step=3&lightbox=1');
                } else {
                    Tools::redirect($this->context->link->getModuleLink('payplug', 'payment', array(), true));
                }
            } elseif ((int)Tools::getValue('lightbox') == 1) {
                Tools::redirect('index.php?controller=order&step=3&lightbox=1');
            }
        } else {
            Tools::redirect('index.php');
        }
    }
}

<?php

/**
 * 
 */

require_once (_PS_ROOT_DIR_.'/modules/vpos/api/VposApi.php');
session_start();


class VposValidationModuleFrontController extends ModuleFrontController
{
    
    public function postProcess()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;

        
        if($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 || $this->context->cart->id_address_invoice == 0 || !$this->module->active){
            Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
        }

        # Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach(Module::getPaymentModules() as $module)
        {
            if($module['name'] == 'vpos')
            {
                $authorized = true;
                break;
            }
        }

        if(!$authorized)
        {
            die(Tools::displayError('This payment method is not available.'));
        }

        $customer = new Customer($this->context->cart->id_customer);
        if(!Validate::isLoadedObject($customer)){
            Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
        }

        $currency = $this->context->currency;
        $total = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        # J.E: si NO esta definido el pago, este vuelve a crear la compra o proceso
        # Este caso ocurre con SingleBuy
        if(!isset($_REQUEST['mode_status'])) 
        {
            VposApi::SendData($publickey, $privatekey, $_REQUEST['id_cart'], $cartOrderTotal, $currency, $mode);
            
        } elseif (isset($_REQUEST['mode_status']) && $_REQUEST['mode_status'] == 'sucess') {
            
            Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.$_REQUEST['id_cart'].'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder);
        } else {
            if($_REQUEST['status'] == 'payment_fail'){

                VposApi::rollBack($publickey, $privatekey, $_REQUEST['id_cart'], $mode);
                
                Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder);
            }
            // Si sale el request con fallo de pago tambien hacer rollback
            VposApi::rollBack($publickey, $privatekey, $_REQUEST['id_cart'], $mode);

            Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?key='.$customer->secure_key.'&id_cart='.(int)$this->context->cart->id.'&id_module='.(int)$this->module->id.'&id_order='.(int)$this->module->currentOrder);
        }
    }
}



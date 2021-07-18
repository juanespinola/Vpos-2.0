<?php 


/**
 * 
 */

require_once (_PS_ROOT_DIR_.'/modules/vpos/api/VposApi.php');

session_start();

class VposPaymentzimpleModuleFrontController extends ModuleFrontController
{
	public function postProcess()
    {
        $this->display_column_left = false;
        $this->display_column_right = false;

        $cart = $this->context->cart;
        $customer = $this->context->customer;
        $currency = new Currency($cart->id_currency);
        $curr_sign = $currency->getSign();
        $publickey = Configuration::get('VPOS_CLAVE_PUBLICA');
        $privatekey = Configuration::get('VPOS_CLAVE_PRIVADA');
        $mode = Configuration::get('VPOS_AMBIENTE');
        $cartId = $cart->id;
        $cartOrderTotal = $cart->getOrderTotal(true, Cart::BOTH);
        $vposProcessId = '';
        $customerId = $customer->id;
        $currIsoCode = $currency->iso_code;

        # J.E: Condicion para ver que selecciona el usuario
        if(empty($vposProcessId))
        {
            $vposProcessId = VposApi::SendData($publickey, $privatekey, $cartId, $cartOrderTotal, $currIsoCode, $mode, 'S', $_POST['nrotel']);
            //$vposProcessId = $vpos->SingleBuy($publickey, $privatekey, $cartId, $cartOrderTotal, $currIsoCode, $mode);
        }


        echo $vposProcessId;
        die;
    }
}
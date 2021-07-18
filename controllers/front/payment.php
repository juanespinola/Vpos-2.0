<?php 


/**
 * 
 */

require_once (_PS_ROOT_DIR_.'/modules/vpos/api/VposApi.php');

session_start();

class VposPaymentModuleFrontController extends ModuleFrontController
{
	
	
	public $ssl = true;
	public function initContent()
	{
		$this->display_column_left = false;
        parent::initContent();

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

        $vpos = new VposApi();

        # J.E: en caso de que devuelva error, lanza un mensaje
        if (Tools::getValue('res') == 'e') {
            $payment_error = $this->getTranslator()->trans("<p>Error al proceder al pago, verifique con el administrador del sitio</p>", array(), 'Modules.Vpos');
        } else {
            $payment_error = '';
        }

        # J.E: Condicion para ver que selecciona el usuario
        if(empty($vposProcessId))
        {
        	$vposProcessId = VposApi::SendData($publickey, $privatekey, $cartId, $cartOrderTotal, $currIsoCode, $mode);
        	//$vposProcessId = $vpos->SingleBuy($publickey, $privatekey, $cartId, $cartOrderTotal, $currIsoCode, $mode);
        }

        /*  contiene la respuesta de cuando se inserta una tarjeta
        *   esto viene de vposApi de la funcion addNewCard
        *   
        */ 
        $response = '';
        if(@$_GET['status'] == 'add_new_card_success')
        {
            $response = $_GET['status'];
            // echo $response;
        } elseif(@$_GET['status'] == 'add_new_card_fail') {
            $response = @$_GET['description'];
            // echo $response;
        }


        // ENVIO DE DATOS AL TEMPLATE
        $this->context->smarty->assign('datos', array(
            'cartid' => $cartId,
            'responseAddCard' => $response,
            'customer' => $customer,
        	'Processid' => $vposProcessId,
        	'mode' => $mode,
            'tpl_dir' => _PS_THEME_DIR_,
            'months' => $months,
            'sign' => $curr_sign,
            'years' => $years,
            'payment_error' => $payment_error,
            'id_lang' => Tools::getValue('id_lang'),
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'nbProducts' => $cart->nbProducts(),
            'cust_currency' => $cart->id_currency,
            'currencies' => $this->module->getCurrency((int)$cart->id_currency),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'this_path' => $this->module->getPathUri(),
            'this_path_bw' => $this->module->getPathUri(),
            'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));
        $this->setTemplate('module:vpos/views/templates/front/payment_execution.tpl');
	}
}
<?php 


/**
 * 
 */

require_once (_PS_ROOT_DIR_.'/modules/vpos/api/VposApi.php');

session_start();

class VposPaymentcardModuleFrontController extends ModuleFrontController
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
        $customerEmail = $customer->email;
        $currIsoCode = $currency->iso_code;
        $cardid = strtotime(date('Y-m-d H:m:s'));
        // tener en cuenta que debe estar registrado para guardar las tarjetas
        //$publickey, $privatekey, $cardid, $userid, $useremail, $return_url = ''
        // utilizar switch
        if($_POST['tipoaccion'] == 'addcard')
        {
            $addCard = VposApi::addNewCard($publickey, $privatekey, $cardid, $customerId, $customerEmail, $mode);
            echo $addCard;
            die;

        } 

        if($_POST['tipoaccion'] == 'getcards')
        {
            $cards = VposApi::getCards($publickey, $privatekey, $customerId, $mode);
            echo $cards;
            die;
        }

        if($_POST['tipoaccion'] == 'deletecards')
        {
            $deleteCard = VposApi::deleteCard($publickey, $privatekey, $_POST['userid'], $_POST['aliastoken'], $mode);
            echo $deleteCard;
            die;
        }
        
        if($_POST['tipoaccion'] == 'paymentcards')
        {
            $paymentWithCard = VposApi::paymentSaveCard($publickey, $privatekey, $_POST['aliastoken'], $cartOrderTotal, $cartId, $currIsoCode, $mode);
            echo $paymentWithCard;
            die;
        }        
    }
}
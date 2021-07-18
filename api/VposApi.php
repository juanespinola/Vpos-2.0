<?php


/**
 * FUNCIONES PARA GENERAR EL PAGO PARA ENVIAR A BANCARD
 */

class VposApi 
{
	
	public function __construct()
	{
		ob_start();
        require_once dirname(dirname(dirname(dirname(__FILE__)))).'/config/config.inc.php';
        require_once dirname(dirname(dirname(dirname(__FILE__)))).'/init.php';
	}


	/**
	 * FUNCIONES PARA ENVIAR DATOS A BANCARD SIN CATASTRAR TARJETA
	 */
	public static function SendData($publickey, $privatekey, $cartid, $amount, $currency, $mode, $zimple='', $additional_data = '')
	{
		$cartId = $cartid;
        $description = "Carrito #".$cartid;
        $processId = time().$cartid;
		$amount = number_format($amount, 2, '.', ''); #FORMATEA EL MONTO
		$token = md5($privatekey.$processId.$amount.$currency);
		$urlbase = _PS_BASE_URL_.__PS_BASE_URI__;
		$returnsuccess = "index.php?fc=module&module=vpos&controller=validation&mode_status=sucess&id_cart=";
        $returncancel = "index.php?fc=module&module=vpos&controller=validation&mode_status=cancel&shop_id=";
        $data = '{
        	"public_key":  "'.$publickey.'" ,
	        "operation": {
		        "token":  "'.$token.'" ,
		        "shop_process_id":'.$processId.',
		        "amount":  "'.$amount.'",
		        "currency":  "'.$currency.'" ,
		        "additional_data":  "'.$additional_data.'" ,
		        "description":   "'.$description.'"  ,
		        "return_url":  "'.$urlbase.$returnsuccess.$cartid.'&shop_id='.$processId.'",
		        "cancel_url":  "'.$urlbase.$returncancel.$processId.'",
                "zimple": "'.$zimple.'"
	        }
	    }';

	    if($mode == 'si')
	    {
            $url = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/single_buy';
        } else {
            $url = 'https://vpos.infonet.com.py/vpos/api/0.3/single_buy';
        }
        # J.E: ENVIO POR JSON A BANCARD
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        $data_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($data));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $data_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        if (empty($buffer)) {
        	# J.E: si trae vacio, direcciona a pagina de error
            Tools::redirect('index.php?fc=module&module=vpos&controller=payment&res=e');
        } else {

            $sucess = Tools::jsondecode($buffer);
            if($sucess->status == 'success' && $sucess->process_id != '')
            {
            	$date = date('Y-m-d H:i:s');
                $current_date = strtotime($date);
                $future_date = $current_date + (60 * 15);
                $format_date = date('Y-m-d H:i:s', $future_date);
                $sql = 'INSERT INTO '._DB_PREFIX_.'vpos_request SET
                	shop_process_id = "'.pSQL($processId).'",
                	cart_id = '.(int)$cartid.',
                	date_added = "'.date('Y-m-d H:i:s').'",
                	rollback_time = "'.$format_date.'"';
                Db::getInstance()->execute($sql);
                
                return $sucess->process_id; 

            } else {
             //    echo "<pre>";
             //    print_r($sucess);
             //    echo "</pre>";
             //    print_r($data);
            Tools::redirect('index.php?fc=module&module=vpos&controller=payment&res=e');
            }
        }
	}

    public static function rollBack($publickey, $privatekey, $cartid, $mode)
    {
        $rollback = 'rollback';
        $amount = '0.00';
        $token = md5($privatekey.$cartid.$rollback.$amount);
        $data = '{
            "public_key":  "'.$publickey.'" ,
            "operation": {
                    "token":  "'.$token.'" ,
                    "shop_process_id":'.$cartid.'
                }
            }';
        if($mode == 'si')
        {
            $url = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/single_buy/rollback';
        }
        else
        {
            $url = 'https://vpos.infonet.com.py/vpos/api/0.3/single_buy/rollback';
        }

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        $cur_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($data));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $cur_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        if(empty($buffer))
        {
            # J.E: si trae vacio, direcciona a pagina de error
            Tools::redirect('index.php?fc=module&module=vpos&controller=payment&res=e');
        }
        else
        {
            $sucess = Tools::jsondecode($buffer);
            // aqui debe hacer update en la base de datos 
            $sql = 'UPDATE '._DB_PREFIX_.'vpos_request SET
                    rollback_time = "'.date('Y-m-d H:i:s').'"
                    WHERE cart_id = '.(int)$cartid;
                Db::getInstance()->execute($sql);
            return $sucess;
        }
    }

    public function addNewCard($publickey, $privatekey, $cardid, $userid, $useremail, $mode, $return_url = '')
    {
        
        $token = md5($privatekey.$cardid.$userid.'request_new_card');

        if ($return_url == '') {
           $return_url = "index.php?fc=module&module=vpos&controller=payment?to=$cardid";
        }

        $json = '{
                    "public_key": "'.$publickey.'",
                    "operation": {
                        "token": "'.$token.'",
                        "card_id": '.$cardid.',
                        "user_id": '.$userid.',
                        "user_cell_phone": 981211030,
                        "user_mail": "'.$useremail.'",
                        "return_url": "'.$return_url.'"
                    }
                }';

        $url = '';
        if($mode == 'si')
        {
            $url = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/cards/new';
        }
        else
        {
            $url = 'https://vpos.infonet.com.py/vpos/api/0.3/cards/new';
        }
       
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $json);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $pass_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($json));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $pass_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        //echo $json;
        if(empty($buffer))
        {
            # J.E: si trae vacio, direcciona a pagina de error
            Tools::redirect('index.php?fc=module&module=vpos&controller=payment&res=e');
        } else  {
            $sucess = Tools::jsondecode($buffer);
            if($sucess->status == 'success')
            {
                
                //Guardar en la base de datos el token que no hay.
                $sql = 'INSERT INTO '._DB_PREFIX_.'vpos_card SET
                    card_id = "'.$cardid.'",
                    user_id = '.$userid.',
                    token = "'.$token.'",
                    user_cell = "0981211030",
                    user_mail = "'.$useremail.'",
                    date_added = "'.date('Y-m-d H:i:s');
                Db::getInstance()->execute($sql);

                echo $sucess->process_id;

            } else {
                echo $sucess->messages[0]->dsc; 
            }
        }
    }

   
    //Se obtiene el listado de tarjetas catastradas.
    public function getCards($publickey, $privatekey, $userid, $mode)
    {
        
        $url = '';
        if($mode == 'si')
        {
            $url_listado = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/users/'.$userid.'/cards';
        } else {
            $url_listado = 'https://vpos.infonet.com.py/vpos/api/0.3/users/'.$userid.'/cards';
        }
        
        $token_curl = md5($privatekey.$userid.'request_user_cards');

        $datos = '{
                    "public_key": "'.$publickey.'", 
                    "operation": {
                            "token": "'.$token_curl.'" 
                        }
                    }';

        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url_listado);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $datos);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $pass_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($datos));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $pass_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        
        if(empty($buffer))
        {
            echo "error";
        } else {
            echo $buffer;
        }
    }

     
    public function deleteCard($publickey, $privatekey, $userid, $aliasToken, $mode)
    {
                
        $token = md5($privatekey."delete_card".$userid.$aliasToken);
        $data = '{
                    "public_key": "'.$publickey.'",
                    "operation": {
                        "token": "'.$token.'",
                        "alias_token": "'.$aliasToken.'" 
                    }
                }';
        $url = '';
        if($mode == 'si')
        {       
            $url = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/users/'.$userid.'/cards';
        } else {
            $url = 'https://vpos.infonet.com.py/vpos/api/0.3/users/'.$userid.'/cards';
        }
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        $pass_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($data));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $pass_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        
        if(empty($buffer))
        {
            echo "error";
        } else {
            echo 'success';
        }
    }

    public function paymentSaveCard($publickey, $privatekey, $aliasToken, $total, $cartid, $currency, $mode)
    {
        
        $processId = time().$cartid;
        $total = $total.".00";//number_format($total, 2, '.', '');
        $token = md5($privatekey.$processId."charge".$total.$currency.$aliasToken);
        
        $data = '{
                    "public_key": "'.$publickey.'", 
                    "operation": {
                        "token": "'.$token.'", 
                        "shop_process_id": '.$processId.',
                        "amount": "'.$total.'",
                        "number_of_payments": 1,
                        "currency": "'.$currency.'",
                        "additional_data": "",
                        "description": "Venta Token",
                        "alias_token": "'.$aliasToken.'"
                    } 
                }';

        $url = '';
        if($mode == 'si')
        {       
            $url = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/charge';
        } else {
            $url = 'https://vpos.infonet.com.py/vpos/api/0.3/charge';
        }
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, true);
        $pass_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($data));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $pass_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);
        
        
        if(empty($buffer))
        {
            echo "esta vacio";
        } else {

            $sucess = Tools::jsondecode($buffer);
            
            if($sucess->status == 'success')
            {
                
                $date = date('Y-m-d H:i:s');
                $current_date = strtotime($date);
                $future_date = $current_date + (60 * 15);
                $format_date = date('Y-m-d H:i:s', $future_date);
                $sql = 'INSERT INTO '._DB_PREFIX_.'vpos_request SET
                    shop_process_id = "'.pSQL($processId).'",
                    cart_id = '.(int)$cartid.',
                    date_added = "'.date('Y-m-d H:i:s').'",
                    rollback_time = "'.$format_date.'"';
                Db::getInstance()->execute($sql);
                
                echo 'sucess';
                die;
                
            } else {

                echo $sucess->messages[0]->dsc; 
                die;
            } 
        }
    }


    //Funcion de compra de la tarjeta sin guardar
    public static function confirmBuy($publickey, $privatekey, $cardid, $id_order, $amount, $mode)
    {
        
        $amount = number_format($amount, 2, '.', '');
        $token = md5($privatekey.$cardid.'get_confirmation');
        $processId = $cardid;//time().$cardid;

        $data = '{
            "public_key":  "'.$publickey.'" ,
            "operation": {
                "token":  "'.$token.'" ,
                "shop_process_id": "'.$processId.'"
            }
        }';
        
        if($mode == 'si')
        {
            $url = 'https://vpos.infonet.com.py:8888/vpos/api/0.3/single_buy/confirmations';
        } else {
            $url = 'https://vpos.infonet.com.py/vpos/api/0.3/single_buy/confirmations';
        }
        
        $curl_handle = curl_init();
        curl_setopt($curl_handle, CURLOPT_URL, $url);
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
        $pass_arr = array('Content-Type: application/json','Content-Length: '.Tools::strlen($data));
        curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $pass_arr);
        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        //$objOrder = new Order($id_order);
        //$history = new OrderHistory();
        //$history->id_order = (int)$objOrder->id;
        //$order = new Order($id_order);
        $history = new OrderHistory();
        $history->id_order = (int)$id_order;

        if(empty($buffer))
        {
            //$history->changeIdOrderState(_PS_OS_ERROR_, (int)($objOrder->id));
            echo 'esta vacio';
        } else {
            $sucess = Tools::jsondecode($buffer);           
            if($sucess->confirmation->response == 'S' && $sucess->confirmation->response_code == '00')
            {
                
                ## JE: Actualiza la orden en base al carrito
                //$id_order = Order::getOrderByCartId((int)$this->context->cart->id);
                $history->changeIdOrderState(Configuration::get('VPOS_PAYMENT_COMPLETE'), (int)($history->id_order));
                //return Tools::displayError('Order Confirmada');
                echo 'success';
            } else {
                $history->changeIdOrderState(_PS_OS_ERROR_, (int)($history->id_order));
                //return Tools::displayError('Se encontro un error, por favor contacte con soporte');
                echo 'error';
            }
        }
    }
}
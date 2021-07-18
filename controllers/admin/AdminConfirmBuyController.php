<?php

require_once (_PS_ROOT_DIR_.'/modules/vpos/api/VposApi.php');
            
class AdminConfirmBuyController extends ModuleAdminController
{
    public function ajaxProcessAdminConfirmBuy()
    {
        
        $response = 'No se encontró la orden inicial';
        
        if(isset($_POST['orderid']))
        {
			
            $publickey = Configuration::get('VPOS_CLAVE_PUBLICA');
            $privatekey = Configuration::get('VPOS_CLAVE_PRIVADA');
            $mode = Configuration::get('VPOS_AMBIENTE');
            $order = new Order($_POST['orderid']);
            if($order){
                
                $cardIdOrder = (int)$order->id_cart;
                $cart  = new Cart($cardIdOrder);
                $total = (float)$cart->getOrderTotal(true, Cart::BOTH);
                
                
                # J.E: select para obtener processId 
                
                $sql = 'SELECT shop_process_id
                        FROM '._DB_PREFIX_.'vpos_response vr
                        LEFT OUTER JOIN '._DB_PREFIX_.'vpos_request vrr ON vrr.cart_id = vr.cart_id
                        WHERE vr.cart_id = '.$cardIdOrder.' ORDER BY date_added DESC';

                $processId = Db::getInstance()->executeS($sql);

                if(!empty($processId)){

                    $response = VposApi::confirmBuy($publickey, $privatekey, $processId[0]['shop_process_id'], $_POST['orderid'], $total, $mode);
                    // $response = json_encode($processId). ' => '. $processId[0]['shop_process_id'];
                } else {
                    $response = 'No se encontró la orden';
                }
            }
        }

        echo $response;
        die;
    }
}


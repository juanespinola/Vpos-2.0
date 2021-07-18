<?php





ob_start();

require_once dirname(dirname(dirname(__FILE__))).'/config/config.inc.php';
require_once dirname(dirname(dirname(__FILE__))).'/init.php';
require_once dirname(__FILE__).'/vpos.php';

$vpos = new Vpos();
$json = Tools::file_get_contents('php://input');

$data = Tools::jsonDecode($json);

if ($data) {
    if(isset($data->operation->shop_process_id) && $data->operation->shop_process_id != ''){

        $cart_id = Tools::substr($data->operation->shop_process_id, 10);
        $total = $data->operation->amount;

        $query = 'INSERT INTO '._DB_PREFIX_.'vpos_response SET
        cart_id = "'.$cart_id.'",
        order_number = "'.$cart_id.'",
        response_code = "'.pSQL($data->operation->response).'",
        response_description = "'.pSQL($data->operation->response_description).'",
        authorization_number = '.pSQL($data->operation->authorization_number);

        $banresponse = Db::getInstance()->execute($query);

        if ($data->operation->response == 'S' && $data->operation->response_code == '00') {
            
            $vpos_waiting = Configuration::get('VPOS_PAYMENT_WAITING');
            $vpos->validateOrder($cart_id, $vpos_waiting, $total, $vpos->displayName);
            
            $response = array(
                "status" => "success", 
                "cart_id" => $cart_id,
            );
            
            echo Tools::jsonEncode($response);
            die;

        } else {

            $vpos->validateOrder($cart_id, _PS_OS_ERROR_, $total, $vpos->displayName);
            $response = array(
                "status" => "failure"
            );
        
            echo Tools::jsonEncode($response);
            die;
        }
    }
}
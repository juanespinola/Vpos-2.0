<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class Vpos extends PaymentModule
{	
	function __construct()
	{
		$this->name = 'vpos';
	    $this->tab = 'payments_gateways';
	    $this->version = '2.0.0';
	    $this->author = 'J.E.E.A';
	    $this->need_instance = 0;
	    $this->ps_versions_compliancy = [
	        'min' => '1.6',
	        'max' => _PS_VERSION_
	    ];
	    $this->bootstrap = true;
	    $this->controllers = array('payment', 'validation', 'paymentzimple', 'paymentcard');

	    parent::__construct();


	    $this->displayName = $this->l('VPOS BANCARD');
	    $this->description = $this->l('Módulo de Bancard, Vpos 2.0');
	    $this->confirmUninstall = $this->l('Realmente desea desinstalar el módulo?');
	}



	public function install()
	{
		include(dirname(__FILE__).'/sql/install.php');
		if(!parent::install()
			|| !$this->registerHook('paymentOptions')
			|| !$this->registerHook('header')
			|| !$this->createAjaxController()
			|| !$this->registerHook('displayAdminOrder'))
			return false;
		
		$this->createOrderState();
		return true;
		
				
	}

	public function uninstall()
	{
		include(dirname(__FILE__).'/sql/uninstall.php');
		if(!parent::uninstall()
		|| !$this->removeAjaxController())
			return false;
		
		$this->deleteOrderState();
		return true;

		

	}

	/*
	*	FUNCIONES PARA CREAR EL CONTROLADOR QUE UTILIZA AJAX
	*/ 
	public function createAjaxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $languages = Language::getLanguages(false);
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $tab->name[$language['id_lang']] = 'AdminConfirmBuy';
            }
        }
        $tab->class_name = 'AdminConfirmBuy';
        $tab->module = $this->name;
        $tab->id_parent = - 1;
        return (bool)$tab->add();
    }

    public function removeAjaxController()
    {
        if ($tab_id = (int)Tab::getIdFromClassName('AdminConfirmBuy')) {
            $tab = new Tab($tab_id);
            $tab->delete();
        }
        return true;
    }




	/*
	*	ESTA ES LA FUNCION PARA QUE APAREZCA EL TEXTO
	*	EN LOS CUADROS DE PAGO Y HAGA REDIR AL ARCHIVO DE PAYMENT 
	*/
	public function hookPaymentOptions()
	{
		
		# J.E: crea una opcion y al hacer clic da redir a payment
		$newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;

		$newOption->setCallToActionText($this->getTranslator()->trans('Pagar con Tarjeta de Crédito o Débito', array(), 'Modules.Vpos'));
        $newOption->setAction($this->context->link->getModuleLink($this->name, 'payment', array(), true));
        //$newOption->setAdditionalInformation($info); para agregar mas informacion
        $payment_options = array();
        $payment_options[] = $newOption;

        return $payment_options;
	}


	/*
	*	ESTA ES LA FUNCION PARA EL ARCHIVO DE CONFIGURACION
	*	AQUI SE CONFIGURA LAS CLAVES Y LA RUTA DE RESPUESTA 
	*/
	public function getContent()
    {
        
        
        $this->smarty->assign('alertsave',false);

        if(Tools::isSubmit('save'))
        {
            $getpublickey = Tools::getValue('publickey');
            $getprivatekey = Tools::getValue('privatekey');
            $geturlresponse = Tools::getValue('urlresponse');
            $mode = Tools::getValue('mode');
            
            Configuration::updateValue('VPOS_CLAVE_PUBLICA', $getpublickey);
            Configuration::updateValue('VPOS_CLAVE_PRIVADA', $getprivatekey);
            Configuration::updateValue('VPOS_URL_RESPUESTA', $geturlresponse);
            Configuration::updateValue('VPOS_AMBIENTE', $mode);

            $this->smarty->assign('alertsave', true);
        }

        $getpublickey = Configuration::get('VPOS_CLAVE_PUBLICA');
        $getprivatekey = Configuration::get('VPOS_CLAVE_PRIVADA');
        //$link = 'https://www.comentalos.com/prestashop/modules/vpos/vpos_response.php';
        $link = _PS_BASE_URL_.__PS_BASE_URI__.'modules/vpos/vpos_response.php';
        $mode = Configuration::get('VPOS_AMBIENTE');
        
        $this->smarty->assign('publickey', $getpublickey);
        $this->smarty->assign('privatekey', $getprivatekey);
        $this->smarty->assign('urlresponse', $link);
        $this->smarty->assign('mode', $mode);

        return $this->display(__FILE__,'configuracion.tpl');
    }


	private function createOrderState()
	{
		
		Configuration::updateValue('VPOS_PAYMENT_WAITING', '');
		Configuration::updateValue('VPOS_PAYMENT_COMPLETE', '');
		Configuration::updateValue('VPOS_PAYMENT_REJECTED', '');

		$status = array(
			'send_email' => false,
			'invoice' => false,
			'unremovable' => true,
			'paid' => false
		);

		# J.E: Si no esta definido, entonces agregue
		if(!Configuration::get('VPOS_PAYMENT_WAITING')){
			$newOrderState = $this->addOrderState($this->l('BANCARD VPOS PENDIENTE DE PAGO'), '#0404B4', $status);
			Configuration::updateValue('VPOS_PAYMENT_WAITING', $newOrderState);
		}
		if(!Configuration::get('VPOS_PAYMENT_COMPLETE')){
			$status = array(
				'send_email' => true,
				'invoice' => true,
				'unremovable' => true,
				'paid' => true
			);
			$newOrderState = $this->addOrderState($this->l('BANCARD VPOS PAGO COMPLETO'), '#088A29', $status);
			Configuration::updateValue('VPOS_PAYMENT_COMPLETE', $newOrderState);
		}
		if(!Configuration::get('VPOS_PAYMENT_REJECTED')){
			$newOrderState = $this->addOrderState($this->l('BANCARD VPOS RECHAZO DE PAGO'), '#8B0000', $status);
			Configuration::updateValue('VPOS_PAYMENT_REJECTED', $newOrderState);
		}
	}

	/*
	*	FUNCION PARA ELIMINAR de las tablas configuration 
	* 	y orderstate UN ESTADO NUEVO
	*/
	private function deleteOrderState()
	{
		

		$sql = 'DELETE pos, posl FROM '._DB_PREFIX_.'order_state pos';
		$sql .= ' JOIN '._DB_PREFIX_.'order_state_lang posl ON pos.id_order_state = posl.id_order_state';
		$sql .= ' WHERE pos.module_name = "vpos"';
		$execute = Db::getInstance()->execute($sql);	


		$sql = 'DELETE FROM '._DB_PREFIX_.'configuration';
		$sql .= ' WHERE name = "VPOS_PAYMENT_COMPLETE"';
		$sql .= ' OR name = "VPOS_PAYMENT_REJECTED"';
		$sql .= ' OR name = "VPOS_PAYMENT_WAITING"';
		$sql .= ' OR name = "VPOS_CLAVE_PUBLICA"';
		$sql .= ' OR name = "VPOS_CLAVE_PRIVADA"';
		$sql .= ' OR name = "VPOS_URL_RESPUESTA"';
		$sql .= ' OR name = "VPOS_AMBIENTE"';

		$execute = Db::getInstance()->execute($sql);


	}

	public function hookHeader()
	{

	    $this->context->controller->addJS($this->_path.'views/js/vpos.js');
		$this->context->controller->addJS($this->_path.'views/js/alerts.js');

	}

	public function hookDisplayAdminOrder()
    {
        $order_id = Tools::getValue('id_order');
        $order = new Order($order_id);

        if($order->module == 'vpos')
        {
            $vpos_response = $this->getOrderResponse();

            $this->context->smarty->assign(array(
                'orderId' => (int)($order_id),
                'urlConfirmBuy' => $this->context->link->getAdminLink('AdminConfirmBuy'),
                'vpos_response' => $vpos_response
            ));

            return $this->display(__FILE__, 'confirmationbuy.tpl');
        }
    }

    /*
	*	FUNCION QUE OBTIENE LAS ORDENES LOS REGISTROS DE PEDIDOS
    */ 
    public function getOrderResponse()
    {
        $orderid = Tools::getValue('id_order');
        $sql = 'SELECT vr.*
			FROM '._DB_PREFIX_.'vpos_response vr
			LEFT OUTER JOIN '._DB_PREFIX_.'orders o ON vr.cart_id = o.id_cart 
			WHERE o.id_order = '.$orderid;
        
        return Db::getInstance()->executeS($sql);
    }
	/*
	*	FUNCION PARA CREAR UN ESTADO NUEVO
	*/
	private function addOrderState($text, $color, $status)
    {
        $order_state = new OrderState();
        $order_state->name = array();
        foreach (Language::getLanguages() as $language){
            $order_state->name[$language['id_lang']] = $text;
        }
        $order_state->name[$this->context->language->id] = $text;
        $order_state->send_email = $status['send_email'];
        $order_state->module_name = 'vpos';
        $order_state->color = $color;
        $order_state->hidden = false;
        $order_state->unremovable = $status['unremovable'];
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = $status['invoice'];
        $order_state->paid = $status['paid'];
        $order_state->template = 'payment';
        
        $order_state->add();
    	return $order_state->id;
    }



	




}



?>